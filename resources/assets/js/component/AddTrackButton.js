var React = require('react');
var Modal = require('react-modal');
var PickLink = require('./PickLink');

const modalStyle = {
    overlay: {
        backgroundColor: 'rgba(0,0,0,0.5)',
        zIndex: 2000
    },
    content : {
        top: '50%',
        left: '50%',
        right: 'auto',
        bottom: 'auto',
        marginRight: '-50%',
        transform: 'translate(-50%, -50%)',
        border: 'none',
        backgroundColor: 'transparent'
    }
};

const panelStyle = {
    maxWidth: '600px',
    minWidth: '200px'
}

module.exports = class UserList extends React.Component {

    constructor(props){
        super(props);

        this.state = {
            modalIsOpen: false,
            selectedTab: 'link'
        }
    }

    openModal() {
        this.setState({
            modalIsOpen: true
        });
    }

    closeModal() {
        this.setState({
            modalIsOpen: false
        });
    }

    handleClick = () => {
        this.openModal();
    };

    handleAfterOpen = () => {

    };

    handleRequestClose = () => {
        this.closeModal();
    };

    handleOnSelectLink = (track) => {
        console.log(track);
    }

    changeTab (tab) {
        if (this.state.selectedTab != tab){
            this.setState({
                selectedTab: tab
            });
        }
    }

    render () {

        var className = this.props.className;
        var buttonClass = this.props.buttonClass;

        var modalIsOpen = this.state.modalIsOpen;
        var selectedTab = this.state.selectedTab;

        return (
            <span className={(className || '')}>
                <button className={'btn btn-default ' + (buttonClass || '')} onClick={this.handleClick}>Add Track</button>
                <Modal
                    isOpen={modalIsOpen}
                    onAfterOpen={this.handleAfterOpen}
                    onRequestClose={this.handleRequestClose}
                    style={modalStyle}
                >
                    <div className="panel panel-default" style={panelStyle}>
                        <div className="panel-heading">
                            <button type="button" className="close pull-right" aria-label="Close" onClick={this.handleRequestClose}>
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <nav>
                                <ul className="nav nav-pills">
                                    <li className={selectedTab == 'link' ? 'active' : ''}>
                                        <a href="javascript:" onClick={() => this.changeTab('link')}>Link</a>
                                    </li>
                                    <li className={selectedTab == 'file' ? 'active' : ''}>
                                        <a href="javascript:" onClick={() => this.changeTab('file')}>File</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div className="panel-body">
                            <div className={selectedTab == 'link' ? '' : 'hidden'}>
                                <PickLink onSelect={this.handleOnSelectLink} />
                            </div>
                            <div className={selectedTab == 'file' ? '' : 'hidden'}>

                            </div>
                        </div>
                    </div>
                </Modal>
            </span>
        );
    }
}
