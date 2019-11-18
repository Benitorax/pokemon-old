import React, { useState, useEffect } from 'react';
import '../../css/AdventureBattle.css';

const csrfToken = document.getElementById('csrfToken').textContent;

function Button(props) {
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

function Select(props) {
    let info = props.info;

    const [pokemonSelected, setPokemonSelected] = useState(props.info.pokemons[0].id);

    function handleChange(e) {
        setPokemonSelected(e.target.value);
    }

    function tranferNewDataToParent(data) {
        props.onNewData(data);
    }

    function tranferCommandToParent(command) {
        props.onCommandExecuted(command);
    }

    useEffect(() => {
        setPokemonSelected(props.info.pokemons[0].id);
    }, [props.info.pokemons]);

    return (
        <>
            <select id={info.name} name={info.name} className={info.className} onChange={ handleChange } >
                { props.info.pokemons.map((pokemon) =>
                    <option key={pokemon.id} value={pokemon.id}>{pokemon.name} (level {pokemon.level})</option>
                ) }
            </select>
            <div>
            <Button onCommandExecuted={tranferCommandToParent} onNewData={tranferNewDataToParent} data={pokemonSelected} info={info.button} />
        </div>
        </>
    );
}

export function Form(props) {
    if(props.form === null) return '';
    const [showForm, setShowForm] = useState(true);

    function tranferNewDataToParent(data) {
        props.onNewData(data);
        setShowForm(false);
    }

    function tranferCommandToParent(command) {
        props.onCommandExecuted(command);

    }
    
    useEffect(() => {
        setShowForm(true);
    }, [props.form]);

    let fields = props.form.map((field) => {
        if(field.type === 'button') {
            return <Button onNewData={tranferNewDataToParent} onCommandExecuted={tranferCommandToParent} key={field.name} info={field} />

        } else if(field.type === 'select') {
            return <Select onNewData={tranferNewDataToParent} onCommandExecuted={tranferCommandToParent} key={field.name} info={field} />
        }
    });

    return (
        <>
            { showForm === false ? null :
                <form name="adventure_battle" method="post" className="adventure-form form-inline justify-content-around">
                    { fields }
                </form>
            }
        </>
    );
}