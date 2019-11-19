import React from 'react';

const csrfToken = document.getElementById('csrfToken').textContent;

export function Button(props) {
    let info = props.info;

    function handleClick(e) {
        e.preventDefault();
        props.onNewData({ 
            messages: {
                messages: ['...'],
                textColor: 'text-white' 
            }
        });
        props.onCommandExecuted(info.name);
        console.log('request to', info.url, props.data);
        axios.post(info.url, {
            csrfToken: csrfToken,
            pokemonId: props.data
        })
        .then(function (response) {     
            console.log(response.data);
            props.onNewData(response.data);
        })
        .catch(function (error) {
            console.log(error);
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