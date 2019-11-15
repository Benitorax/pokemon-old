import { render } from 'react-dom';
import React, { useState, useEffect, useContext } from 'react';

const csrfToken = document.getElementById('csrfToken').textContent;

function Message(props) {
    let messages = '';
    let textColor = "col-sm-12 p-3 mb-2 border border-secondary rounded-lg bg-dark";

    if(props.messages !== null) {
        messages = props.messages.messages.map((message, index)=>
            <div dangerouslySetInnerHTML={{__html: message}} className="text-center" key={index}></div>
        );
        textColor += ' ' + props.messages.textColor;
    }

    return (
        <div className="row">
            <div className={textColor} style={{minHeight: '90px'}}>
                { messages }
            </div>
        </div>
    );
}

function Button(props) {
    let info = props.info;
    const dataContext = useContext(DataContext);
    function handleClick(e) {
        e.preventDefault();
        console.log('request to ', info.url, props.data);
        axios.post(info.url, {
            csrfToken: csrfToken,
            pokemonId: props.data
        })
        .then(function (response) {     
            console.log(response.data);
            dataContext.updateData(response.data);
        })
        .catch(function (error) {
            console.log(error);
        });
    }

    return (
        <div>
            <button type="submit" id={ info.name } name={ info.name } className={ info.className } onClick={ handleClick }>
                { info.buttonText }
            </button>
        </div>
    );
}

function Select(props) {
    let info = props.info;
    //let pokemonSelected = props.info.pokemons[0];
    const [pokemonSelected, setPokemonSelected] = useState(props.info.pokemons[0].id);

    function handleChange(e) {
        console.log('handleChange', e.target.value);
        setPokemonSelected(e.target.value);
    }

    return (
        <>
            <select id={info.name} name={info.name} className={info.className} onChange={ handleChange }>
                { props.info.pokemons.map((pokemon) =>
                    <option key={pokemon.id} value={pokemon.id}>{pokemon.name} (level {pokemon.level})</option>
                ) }
            </select>
            <div>
            <Button data={pokemonSelected} info={info.button}/>
        </div>
        </>
    );
}

function Form(props) {
    if(props.form === null) return '';

    let fields = props.form.map((field) => {
        if(field.type === 'button') {
            return <Button key={field.name} info={field}/>
        } else if(field.type === 'select') {
            return <Select key={field.name} info={field}/>
        }
    })

    return (
        <form name="adventure_battle" method="post" className="adventure-form form-inline justify-content-center">
            { fields }
        </form>
    );
}

function Opponent(props) {
    let opponent = props.opponent;
    return (
        <>
            <div className="text-right"><strong>{ opponent.name }</strong></div>
            <div className="progress">
                <div className="progress-bar" role="progressbar" style={{width: opponent.healthPoint+"%"}} aria-valuenow={ opponent.healthPoint } aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div className="mr-4"><img className="float-right" src={ opponent.spriteFrontUrl }/></div>
        </>
    );
}

function Player(props) {
    let player = props.player;
    return (
        <>
            <div className="ml-4"><img src={ player.spriteBackUrl }/></div>
            <div className="text-left"><strong>{ player.name }</strong></div>
            <div className="progress">
                <div className="progress-bar" role="progressbar" style={{width: player.healthPoint +"%"}} aria-valuenow={ player.healthPoint } aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </>
    );
}

function BattleScreen(props) {
    let centerImageUrl = null;
    if(props.centerImageUrl !== null) {
        centerImageUrl = (
            <div className="row">
                <img className="mx-auto" src={ props.centerImageUrl }/>
            </div>
        );
    }

    return (
        <div className="row">
            <div style={{minHeight: '300px'}} className="col-sm-12 mb-2 border border-secondary rounded-lg">
                <div className="mt-2">
                    { props.opponent !== null ? <Opponent opponent={props.opponent}/> : null }
                </div><br/><br/><br/><br/>
                { centerImageUrl }
                <div className="mb-3">
                    { props.player !== null ? <Player player={props.player}/> : null }
                </div>
            </div>
        </div>
    );
}

const dataModel = {
    form: null,
    opponent: null,
    player: null,
    messages: null,
    centerImageUrl: null
};

const DataContext = React.createContext(dataModel);

function AdventureBattle() {
    const [data, setData] = useState(dataModel);
    
    if(data.messages === null) {
        axios.get('/adventure/start')
        .then(function (response) {
            console.log('response from AdventureBattle', response.data);
            setData(response.data);
        })
        .catch(function (error) {
            console.log('error', error);
        })
    }

    function updateData($data) {
        setData($data);
    }

    return (
        <DataContext.Provider value={{updateData: updateData}}>
            <div className="col-sm-5">
                <Message messages={data.messages}/>
                <BattleScreen opponent={data.opponent} player={data.player}  centerImageUrl={data.centerImageUrl}/>
                { data.form !== null ? <Form form={data.form}/> : null }
            </div>
        </DataContext.Provider>
    );
}

const battleNode = document.getElementById('r-battle');
render(
    <AdventureBattle/>, 
    battleNode
);

{/* <form name="travel" method="post" className="adventure-form form-inline justify-content-center">
    <div id="travel" className="row">
        <div>
            <button type="submit" id="travel_travel" name="travel[travel]" className="btn btn-outline-secondary">Travel around</button>
        </div>
        <input type="hidden" id="travel__token" name="travel[_token]" value="bJyn9tkkj1Yf4kZ8lKXguHz_d57Mr3V08cm9l-IcrME">
    </div>
</form>

<form name="select_pokemon" method="post" class="adventure-form form-inline justify-content-center">
    <div id="select_pokemon" class="row">
        <div>
            <select id="select_pokemon_choicePokemon" name="select_pokemon[choicePokemon]" class="btn btn-outline-info">
                <option value="f705b324-5db3-45d9-a2eb-44f9c64da62c">Blastoise (level 100)</option>
                <option value="83660164-907c-413b-ab26-70d17814d425">Geodude (level 1)</option>
                <option value="e7bf60f5-fe67-45c2-99dc-11c67a785470">Golbat (level 22)</option>
                <option value="a155723b-a18a-44c6-8f4d-690be2e431ab">Magmar (level 31)</option>
                <option value="bee8bf6b-6e64-46c5-8221-44809a6f726b">Mew (level 18)</option>
                <option value="7f023881-fef6-477d-9039-387f06b889ec">Moltres (level 100)</option>
            </select>
        </div>
    <div>
        <button type="submit" id="select_pokemon_selectPokemon" name="select_pokemon[selectPokemon]" class="btn btn-outline-success">SELECT</button></div>
        <input type="hidden" id="select_pokemon__token" name="select_pokemon[_token]" value="Y6emeCbEN9PFEiEiV2u4fLaLCYu6sjAoiuLVHlYsKvc">            
    </div>
</form>

<form name="adventure_battle" method="post" class="adventure-form form-inline justify-content-center">
    <div id="adventure_battle" class="row">
        <div>
            <button type="submit" id="adventure_battle_attack" name="adventure_battle[attack]" class="btn btn-outline-primary">Attack</button>
        </div>
        <div>
            <button type="submit" id="adventure_battle_heal" name="adventure_battle[heal]" class="btn btn-outline-secondary">Heal</button>
        </div>
        <div><button type="submit" id="adventure_battle_throwPokeball" name="adventure_battle[throwPokeball]" class="btn btn-outline-success">Throw pokeball</button>
        </div>
        <div>
            <button type="submit" id="adventure_battle_leave" name="adventure_battle[leave]" class="btn btn-outline-danger">Leave</button>
        </div>
        <input type="hidden" id="adventure_battle__token" name="adventure_battle[_token]" value="iyoSl4od4AYFIlgwK9CzrA4DRTd-IHd1rTZSnpMlGbk">
    </div>
</form> */}