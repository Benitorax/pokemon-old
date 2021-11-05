import React from 'react';

// const csrfElement = document.getElementById('csrfToken');
// if (csrfElement) {
//     const csrfToken = csrfElement.textContent;
// } else {
//     const csrfToken = '';
// }

export function Button(props) {
    let info = props.info;
    const csrfToken = document.getElementById('csrfToken').textContent;

    function handleClick(e) {
        e.preventDefault();
        props.onCommandExecuted(info.name, {
            url: info.url,
            csrfToken: csrfToken,
            data: props.data
        });
    }

    return (
        <div className="mb-2">
            <button type="submit" id={ info.name } name={ info.name } className={ info.className }  onClick={ handleClick }>
                { info.buttonText }
            </button>
        </div>
    );
}