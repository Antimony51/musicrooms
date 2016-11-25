import React from 'react';

class FileInput extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            text: ''
        };
    }

    handleChange = (ev) => {
        if (this.props.onChange){
            this.props.onChange(ev);
        }

        var files = ev.target.files;
        var text = '';
        if (files.length == 1){
            text = files[0].name;
        }else if (this.files.length > 1){
            text = files.length + ' selected';
        }

        this.setState({
            text: text
        });
    };

    render() {
        return (
            <div className="input-group">
                <label className="input-group-btn">
                    <span className="btn btn-default">
                        Browse <input
                            type="file"
                            id={this.props.id}
                            name={this.props.name}
                            className="hidden"
                            onChange={this.handleChange}
                            multiple={this.props.multiple} />
                    </span>
                </label>
                <input type="text" className="form-control" readOnly value={this.state.text} />
            </div>
        );
    }

}

export default FileInput;
