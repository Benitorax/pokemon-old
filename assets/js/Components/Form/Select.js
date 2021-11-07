import React, { useState, useEffect } from 'react';
import { Button } from './Button';

export function Select(props) {
    let info = props.info;

    const [pokemonSelected, setPokemonSelected] = useState(props.info.pokemons[0].uuid);

    function handleChange(e) {
        setPokemonSelected(e.target.value);
    }

    function tranferCommandToParent(command, data) {
        props.onCommandExecuted(command, data);
    }

    useEffect(() => {
        setPokemonSelected(props.info.pokemons[0].uuid);
    }, [props.info.pokemons]);

    return (
        <>
            <select id={info.name} name={info.name} className={info.className} onChange={ handleChange } >
                { props.info.pokemons.map((pokemon) =>
                    <option key={pokemon.uuid} value={pokemon.uuid}>{pokemon.name} (level {pokemon.level})</option>
                ) }
            </select>
            <div>
            <Button onCommandExecuted={tranferCommandToParent} data={pokemonSelected} info={info.button} />
        </div>
        </>
    );
}