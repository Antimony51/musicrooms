import React from 'react';

function CopyLink (props){
    return (
        <span className={props.className} style={props.style} title="Copy Link">
            <i className="icon-button fa fa-link" onClick={() => copyLink(props.link)} />
        </span>
    )
}

export default CopyLink;
