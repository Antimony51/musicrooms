var React = require('react');

module.exports = class List extends React.Component {

    render () {
        return (
            <div>
                {
                    this.props.items.map((item) => (
                        <div key={this.props['item-key'].split('.').reduce((a,b) => a[b], item)}>
                            {item}
                        </div>
                    ))
                }
            </div>
        );
    }
};
