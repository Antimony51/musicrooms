import React from 'react';

class SeekBar extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            value: props.value,
            tmpValue: null
        };
    }

    bar = null;
    seeking = false;

    barStart = 0;
    barEnd = 0;
    barSize = 0;

    calcInputValue (clientPos) {
        return _.clamp((clientPos - this.barStart) / this.barSize, 0, 1);
    }

    handleMouseDown = (ev) => {
        ev.preventDefault();
        if (!this.props.locked){
            var rect = this.bar.getBoundingClientRect();
            var clientPos;
            if (!this.props.vertical){
                this.barStart = rect.left;
                this.barEnd = rect.right;
                clientPos = ev.clientX;
            }else{
                this.barStart = rect.bottom;
                this.barEnd = rect.top;
                clientPos = ev.clientY;
            }
            this.barSize = this.barEnd - this.barStart;
            var newValue = this.calcInputValue(clientPos);
            if (this.props.immediate){
                if (_.isFunction(this.props.onChange)){
                    this.props.onChange(newValue);
                }
            }
            this.setState({
                tmpValue: newValue
            });
            document.documentElement.addEventListener('mousemove', this.handleMouseMove);
            document.documentElement.addEventListener('mouseup', this.handleMouseUp);
            this.seeking = true;
        }
    };

    handleMouseMove = (ev) => {
        var newValue = this.props.vertical ? this.calcInputValue(ev.clientY) : this.calcInputValue(ev.clientX);
        if (this.props.immediate){
            if (_.isFunction(this.props.onChange)){
                this.props.onChange(newValue);
            }
        }
        this.setState({
            tmpValue: newValue
        });
    };

    handleMouseUp = (ev) => {
        var newValue = this.props.vertical ? this.calcInputValue(ev.clientY) : this.calcInputValue(ev.clientX)
        if (_.isFunction(this.props.onChange)){
            this.props.onChange(newValue);
        }

        this.setState({
            value: newValue,
            tmpValue: null
        });
        document.documentElement.removeEventListener('mousemove', this.handleMouseMove);
        document.documentElement.removeEventListener('mouseup', this.handleMouseUp);
        this.seeking = false;
    };

    componentWillUnmount() {
        if (this.seeking){
            document.documentElement.removeEventListener('mousemove', this.handleMouseMove);
            document.documentElement.removeEventListener('mouseup', this.handleMouseUp);
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            value: nextProps.value
        });
    }

    render() {

        var seek = _.isNull(this.state.tmpValue) ? this.state.value : this.state.tmpValue;
        var vertical = this.props.vertical;
        var locked = this.props.locked;

        var className = 'seek-bar' + (vertical ? ' vertical' : '') + (locked ? ' locked' : '');
        if (this.props.className){
            className += this.props.className
        }
        var style = this.props.style;

        return (
            <div className={className} style={style}
                onMouseDown={this.handleMouseDown} ref={(bar) => this.bar = bar}>
                <div className="bg-bar">
                    {
                        !_.isNil(seek) && <div className="fill-bar" style={
                            vertical ? {height: seek * 100 + '%'} : {width: seek * 100 + '%'}
                        }/>
                    }
                </div>
            </div>
        );
    }

}

export default SeekBar;
