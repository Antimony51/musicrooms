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
    barStartX = 0;
    barEndX = 0;
    barWidth = 0;
    seeking = false;

    calcInputValue (clientX) {
        return _.clamp((clientX - this.barStartX) / this.barWidth, 0, 1);
    }

    handleMouseDown = (ev) => {
        ev.preventDefault();
        if (!this.props.locked){
            var rect = this.bar.getBoundingClientRect();
            this.barStartX = rect.left;
            this.barEndX = rect.right;
            this.barWidth = this.barEndX - this.barStartX;
            this.setState({
                tmpValue: this.calcInputValue(ev.clientX)
            });
            document.documentElement.addEventListener('mousemove', this.handleMouseMove);
            document.documentElement.addEventListener('mouseup', this.handleMouseUp);
            this.seeking = true;
        }
    };

    handleMouseMove = (ev) => {
        this.setState({
            tmpValue: this.calcInputValue(ev.clientX)
        });
    };

    handleMouseUp = (ev) => {
        var newValue = this.calcInputValue(ev.clientX);
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

        return (
            <div className="seek-bar" onMouseDown={this.handleMouseDown} ref={(bar) => this.bar = bar}>
                <div className="bg-bar"></div>
                {
                    !_.isNil(seek) && <div className="fill-bar" style={{width: seek * 100 + '%'}}/>
                }
            </div>
        );
    }

}

export default SeekBar;
