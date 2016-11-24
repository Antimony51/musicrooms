import React from 'react';
import Track from '../class/Track';

class PickFavorite extends React.Component {

    timeout = null;

    constructor(props) {
        super(props);

        this.state = {
            results: null,
            track: null,
            error: null,
            listError: null,
            wait: false
        };
    }

    query = null;
    resultCache = {};

    newValue (value) {
        $.ajax({
            url: `/user/${app.currentUser.name}/favorites/search`,
            method: 'get',
            dataType: 'json',
            data: {
                query: value,
                perPage: 5
            }
        })
            .done((data) => {
                this.query = value;
                this.resultCache = {1: data};
                this.setState({
                    results: data,
                    track: null
                });
            })
            .fail(() => {
                this.setState({ error: 'Search failed.' });
            })
            .always(() => {
                this.setState({ wait: false });
            });
    }

    gotoPage (page) {
        if (this.resultCache[page]){
            this.setState({
                results: this.resultCache[page]
            });
        }else{
            this.setState({
                wait: true
            });
            $.ajax({
                url: `/user/${app.currentUser.name}/favorites/search`,
                method: 'get',
                dataType: 'json',
                data: {
                    query: this.query,
                    perPage: 5,
                    page: page
                }
            })
                .done((data) => {
                    this.resultCache[page] = data;
                    this.setState({
                        results: data,
                        track: null
                    });
                })
                .fail(() => {
                    this.setState({ error: 'Search failed.' });
                })
                .always(() => {
                    this.setState({ wait: false });
                });
        }

    }

    componentDidMount() {
        this.setState({
            wait: true
        });

        this.newValue('');
    }

    handleOnChange = (ev) => {
        this.setState({
            error: null
        });

        clearTimeout(this.timeout);
        this.timeout = null;

        var value = _.trim(ev.target.value);

        this.timeout = setTimeout(() => this.newValue(value), 200);

        this.setState({
            wait: true
        });
    };

    handleSubmit = (ev) => {
        ev.preventDefault();

        if (!this.state.wait){
            if (this.state.track && this.state.track.type && this.state.track.uri){
                this.props.onSelect(this.state.track);
            }else{
                if (!this.state.listError){
                    if (this.state.track){
                        this.setState({
                            error: 'Invalid track.'
                        });
                    }else{
                        this.setState({
                            error: 'Not a valid YouTube or Soundcloud URL'
                        });
                    }
                }
            }
        }
    };

    handleRowClick = (track) => {
        this.setState({track: track});
    }

    render() {
        const type = this.state.track ? this.state.track.type : null;
        const {
            error, listError, wait, results
        } = this.state;

        return (
            <form className="form-horizontal" onSubmit={this.handleSubmit}>
                <div className={ 'form-group' + (error ? ' has-error' : '') }>
                    <div className="col-sm-12">
                        <div className="input-group">
                            <span className="input-group-addon">
                                <i className={
                                    'fa ' + (
                                        wait ? 'fa-spinner fa-spin' : 'fa-search'
                                    )
                                }></i>
                            </span>
                            <input type="text" className="form-control" name="query"
                                onChange={this.handleOnChange} autoComplete="off"
                                ref={(el) => this.input = el}/>
                        </div>
                        {
                            error ? (
                                <span className="help-block">
                                    <strong>{error}</strong>
                                </span>
                            ) : null
                        }
                    </div>
                </div>
                <div className={ 'form-group' + (listError ? ' has-error' : '') }>
                    <div className="col-sm-12">
                        {
                            results && (results.data.length ? (
                                <div>
                                    <table className="table table-striped">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Title</th>
                                                <th>Artist</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {
                                                results.data.map((track) => (
                                                    <tr key={track.id}
                                                        className={'pick-row' + (this.state && this.state.track && this.state.track.id == track.id ? ' info' : '' ) }
                                                        onClick={() => this.handleRowClick(track)}>
                                                        <td className="text-center">
                                                            {
                                                                track.type === 'youtube' ? (
                                                                    <i className="fa fa-youtube color-youtube"></i>
                                                                ) : track.type === 'soundcloud' ? (
                                                                    <i className="fa fa-soundcloud color-soundcloud"></i>
                                                                ) : track.type === 'file' ? (
                                                                    <i className="fa fa-file-audio-o"></i>
                                                                ) : ''
                                                            }
                                                        </td>
                                                        <td>{track.title}</td>
                                                        <td>{track.artist}</td>
                                                    </tr>
                                                ))
                                            }
                                        </tbody>
                                    </table>
                                    <div className="text-center">
                                        <ul className="pagination" style={{margin: 0}}>
                                            {
                                                results.current_page == 1 ? (
                                                    <li className="disabled">
                                                        <span>&laquo;</span>
                                                    </li>
                                                ) : (
                                                    <li>
                                                        <a href="javascript:" rel="prev"
                                                            onClick={() => this.gotoPage(results.current_page-1)}
                                                            >&laquo;</a>
                                                    </li>
                                                )
                                            }
                                            {
                                                (()=>{
                                                    var pageNumbers = [];
                                                    for (let i = 1; i <= results.last_page; i++){
                                                        if (i == 1 || i == results.last_page ||
                                                            _.inRange(i, results.current_page-1, results.current_page+2))
                                                        {
                                                            pageNumbers.push(
                                                                i == results.current_page ? (
                                                                    <li className="active" key={i}>
                                                                        <span>{i}</span>
                                                                    </li>
                                                                ) : (
                                                                    <li key={i}>
                                                                        <a href="javascript:" onClick={() => this.gotoPage(i)}>{i}</a>
                                                                    </li>
                                                                )
                                                            );
                                                        }else{
                                                            let last = _.last(pageNumbers);
                                                            if (!_.isString(last)){
                                                                if (i < results.current_page){
                                                                    pageNumbers.push('gap-a');
                                                                }else{
                                                                    pageNumbers.push('gap-b');
                                                                }
                                                            }
                                                        }
                                                    }
                                                    pageNumbers = pageNumbers.map((v) => {
                                                        if (!_.isString(v)){
                                                            return v;
                                                        }else{
                                                            return (
                                                                <li className="disabled" key={v}>
                                                                    <span>...</span>
                                                                </li>
                                                            );
                                                        }
                                                    })
                                                    return pageNumbers;
                                                })()
                                            }
                                            {
                                                results.current_page == results.last_page ? (
                                                    <li className="disabled">
                                                        <span>&raquo;</span>
                                                    </li>
                                                ) : (
                                                    <li>
                                                        <a href="javascript:" rel="prev"
                                                            onClick={() => this.gotoPage(results.current_page+1)}
                                                            >&raquo;</a>
                                                    </li>
                                                )
                                            }
                                        </ul>
                                    </div>
                                </div>
                            ) : 'No tracks matched your query')
                        }
                        {
                            listError ? (
                                <span className="help-block">
                                    <strong>{listError}</strong>
                                </span>
                            ) : null
                        }
                    </div>
                </div>
                <div className="form-group">
                    <div className="text-center">
                        <button type="submit" className="btn btn-primary" disabled={!this.state.track}>Add Track</button>
                    </div>
                </div>
            </form>
        );
    }
}

export default PickFavorite;
