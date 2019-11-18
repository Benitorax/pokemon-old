import { render } from 'react-dom';
import React, { useState, useEffect, useContext, useCallback } from 'react';
import { Message } from './Message';
import { BattleScreen } from './BattleScreen';
import { Form } from './Form';
import { getDataModel } from './DataModel';

import '../../css/AdventureBattle.css';


export function TournamentBattle() {
    const [data, setData] = useState(getDataModel());
    function updateData($data) {
        setData($prevData => Object.assign({}, $prevData, $data));
    }

    useEffect(() => {
        if(data.messages === null) {
            axios.get('/tournament/start')
            .then(function (response) {
                console.log('response from /tournament/start', response.data);
                updateData(response.data);
            })
            .catch(function (error) {
                console.log('error', error);
            });
        }
    }, []);

    const [command, setCommand] = useState(null);
    function updateCommand(command) {
        setCommand(command);
        if('selectPokemon' === command) {
            showImageOnScreen('ash', 'ash-support');
        } else if(
            (command === 'next' && data.turn === 'opponent') || ['attack', 'heal'].includes(command)
        ) {
            let className = [
                ['ash', 'ash-support'], 
                ['ash', 'ash-crazy'], 
                ['crowd', 'crowd-move']
            ][Math.floor(Math.random() * Math.floor(3))];
            setTimeout(() => showImageOnScreen(className[0], className[1]), 1000);
        }
    }

    const [showImage, setShowImage] = useState(false);
    const [imageObject, setImageObject] = useState(null);
    function showImageOnScreen(url, className) {
        setTimeout(() => setShowImage(false), 1000);
        setImageObject({
            url: '/images/'+ url +'.png',
            className: 'row ' + className
        });
        setShowImage(true);
    }

    return (
        <div className="col-sm-10 col-md-8 col-lg-6 col-xl-5 mt-3">
            <Message messages={data.messages} />
            <BattleScreen 
                pokeballCount={data.pokeballCount} 
                healthPotionCount={data.healthPotionCount} 
                turn={data.turn} 
                opponent={data.opponent} 
                player={data.player} 
                command={command} 
                centerImageUrl={data.centerImageUrl}
            />
            { data.form !== null ? <Form turn={data.turn} onCommandExecuted={updateCommand} onNewData={updateData} form={data.form}/> : null }
            { showImage === false ? null : <div className={imageObject.className} style={{ maxHeight: '0px' }}><img className="mx-auto" src={imageObject.url}/></div> }
        </div>
    );
}

const battleNode = document.getElementById('r-battle');
render(
    <TournamentBattle/>, 
    battleNode
);