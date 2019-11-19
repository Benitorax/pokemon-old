import React, { useState, useEffect } from 'react';
import { Button } from './Button';

export function Select(props) {
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