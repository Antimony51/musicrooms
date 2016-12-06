import React from 'react';
import Modal from 'react-modal';
import PickLink from './PickLink';
import PickFavorite from './PickFavorite';
import PickFile from './PickFile';

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

class AddTrackButton extends React.Component {

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
        var selectedTab = this.state.selectedTab;
        if (selectedTab == 'link'){
            $(this.pickLink.input).focus();
        }else if (selectedTab == 'fav'){
            $(this.pickFavorite.input).focus();
        }
    };

    handleRequestClose = () => {
        this.closeModal();
    };

    handleSelectFile = (files) => {
        this.closeModal();
        this.props.onRequestUpload(files);
    };

    handleSelectTrack = (track) => {
        this.closeModal();
        $.ajax({
            url: '/room/' + app.currentRoom.name + '/addtrack',
            method: 'post',
            data: {
                type: track.type,
                uri: track.uri
            }
        })
            .fail((xhr) => {
                if (xhr.responseText){
                    alertify.alert('Failed to add track', xhr.responseText);
                }else{
                    alertify.alert('Error', 'Failed to add track');
                }
            });
    };

    changeTab (tab) {
        if (this.state.selectedTab != tab){
            this.setState({
                selectedTab: tab
            });
        }
    }

    render () {

        var className = this.props.className;

        var modalIsOpen = this.state.modalIsOpen;
        var selectedTab = this.state.selectedTab;

        return (
            <span className={(className || '')}>
                <i className="icon-button fa fa-plus text-success" title="Add Track" onClick={this.handleClick} />
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
                                    <li className={selectedTab == 'fav' ? 'active' : ''}>
                                        <a href="javascript:" onClick={() => this.changeTab('fav')}>Favorite</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div className="panel-body">
                            <div className={selectedTab == 'link' ? '' : 'hidden'}>
                                <PickLink ref={(el) => this.pickLink = el} onSelect={this.handleSelectTrack} />
                            </div>
                            <div className={selectedTab == 'file' ? '' : 'hidden'}>
                                <PickFile onSelect={this.handleSelectFile} />
                            </div>
                            <div className={selectedTab == 'fav' ? '' : 'hidden'}>
                                <PickFavorite ref={(el) => this.pickFavorite = el} onSelect={this.handleSelectTrack} />
                            </div>
                        </div>
                    </div>
                </Modal>
            </span>
        );
    }
}

export default AddTrackButton;
