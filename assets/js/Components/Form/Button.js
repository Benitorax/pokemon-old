import React from 'react';

const csrfToken = document.getElementById('csrfToken').textContent;

export function Button(props) {
    let info = props.info;

    function handleClick(e) {
        e.preventDefault();
        props.onCommandExecuted(info.name, {
            url: info.url,
            csrfToken: csrfToken,
            data: props.data
        });
    }

    return (
        <div>
            <button type="submit" id={ info.name } name={ info.name } className={ info.className }  onClick={ handleClick }>
                { info.buttonText }
            </button>
        </div>
    );
}