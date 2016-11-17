import React from 'react';

class ScrollyText extends React.Component {

    lastScrollWidth = 0;

    constructor(props) {
        super(props);
        this.state = {
            scrollLeft: 0,
        };
    }

    handleMouseMove = (ev) => {
        var scrollWidth = this.element.scrollWidth;
        var clientWidth = this.element.clientWidth;
        var maxScroll = scrollWidth - clientWidth;
        if (maxScroll > 0){
            var rect = this.element.getBoundingClientRect();
            var width = rect.right - rect.left;
            var pos = _.clamp((ev.clientX - (0.1 * width) - rect.left) / (width * 0.8), 0, 1);
            this.setState({
                scrollLeft: pos * maxScroll
            });
        }else{
            this.setState({
                scrollLeft: 0
            });
        }
    };

    handleMouseLeave = (ev) => {
        this.setState({
            scrollLeft: 0
        });
    };

    handleWindowResize = () => {
        this.forceUpdate();
    };

    componentDidMount() {
        window.addEventListener('resize', this.handleWindowResize);
    }

    componentWillUnmount() {
        window.removeEventListener('resize', this.handleWindowResize);
    }

    componentDidUpdate(prevProps, prevState) {
        if (this.element && this.element.scrollWidth != this.lastScrollWidth){
            this.forceUpdate();
        }
    }

    render() {
        const {
            scrollLeft
        } = this.state;
        var scrollWidth = 0,
            clientWidth = 0,
            clientHeight = 0;
        if (this.element){
            this.element.scrollLeft = scrollLeft;
            scrollWidth = this.element.scrollWidth;
            clientWidth = this.element.clientWidth;
            clientHeight = this.element.clientHeight;
            this.lastScrollWidth = scrollWidth;
        }
        var maxScroll = scrollWidth - clientWidth,
            leftFade = 0,
            rightFade = 0,
            fadeWidth = clientWidth*0.1,
            fadeHeight = clientHeight;
        if (maxScroll > 0){
            leftFade = Math.min(scrollLeft / (maxScroll*0.1), 1);
            rightFade = Math.min((maxScroll - scrollLeft) / (maxScroll*0.1), 1);
        }
        return (
            <div className={this.props.className} style={_.assign({position: 'relative'}, this.props.style)}
                 onMouseMove={this.handleMouseMove}
                 onMouseLeave={this.handleMouseLeave}>
                <div className="scrolly-fade-left"
                     style = {{
                         width: leftFade * fadeWidth + 'px',
                         height: fadeHeight + 'px'
                     }}
                />
                <div className="scrolly-fade-right"
                     style = {{
                         width: rightFade * fadeWidth + 'px',
                         height: fadeHeight + 'px'
                     }}
                />
                <div className={'scrolly-text'}
                     ref={(element) => this.element = element}>
                    {this.props.children}
                </div>
            </div>
        );
    }

}

export default ScrollyText;
