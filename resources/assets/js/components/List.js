var React = require('react');

module.exports = class List extends React.Component {

    render () {
        return (
            <div>
                {
                    this.props.items.map((item, key) => (
                        <div key={key}>{item}</div>
                    ))
                }
            </div>
        );
    }
};