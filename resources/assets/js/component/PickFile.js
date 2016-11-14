import React from 'react';
import Track from '../class/Track';

class PickFile extends React.Component {

    timeout = null;

    constructor(props) {
        super(props);

        this.state = {
            track: null,
            error: null,
            wait: false
        };
    }

    handleSubmit = (ev) => {
        ev.preventDefault();

        if (!this.state.wait){
            if (this.state.track && this.state.track.type && this.state.track.uri){
                this.props.onSelect(this.state.track);
            }else{
                if (!this.state.error){
                    this.setState({
                        error: 'Not a valid YouTube or Soundcloud URL'
                    });
                }
            }
        }
    };

    render() {
        var type = this.state.track ? this.state.track.type : null;
        var error = this.state.error;
        var wait = this.state.wait;

        return (
            <form className="form-horizontal" onSubmit={this.handleSubmit}>
                <div className={ 'form-group' + (error ? ' has-error' : '') }>
                    <div className="col-md-12">
                        <input type="file" className="form-control" name="audiofile" />
                        {
                            error ? (
                                <span className="help-block">
                                    <strong>{error}</strong>
                                </span>
                            ) : null
                        }
                    </div>
                </div>
                <div className="form-group">
                    <div className="col-md-12 text-center">
                        <button type="submit">Upload</button>
                    </div>
                </div>
            </form>
        );
    }
}

export default PickFile;
