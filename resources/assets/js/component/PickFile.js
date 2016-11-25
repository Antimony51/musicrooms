import React from 'react';
import Track from '../class/Track';
import FileInput from './FileInput';

class PickFile extends React.Component {

    timeout = null;

    constructor(props) {
        super(props);

        this.state = {
            files: [],
            error: null
        };
    }

    handleChange = (ev) => {
        this.setState({
            files: ev.target.files
        });
    }

    handleSubmit = (ev) => {
        ev.preventDefault();

        var files = this.state.files;
        if (files.length){
            this.props.onSelect(files);
        }else{
            if (!this.state.error){
                this.setState({
                    error: 'No files selected'
                });
            }
        }
    };

    render() {
        var error = this.state.error;
        var hasFiles = this.state.files.length > 0;

        return (
            <form className="form-horizontal" onSubmit={this.handleSubmit}>
                <div className={ 'form-group' + (error ? ' has-error' : '') }>
                    <div className="col-md-12">
                        <FileInput name="file" onChange={this.handleChange} multiple />
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
                        <button className="btn btn-primary" type="submit" disabled={!hasFiles}>Upload</button>
                    </div>
                </div>
            </form>
        );
    }
}

export default PickFile;
