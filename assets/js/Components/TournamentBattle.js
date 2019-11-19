import { render } from 'react-dom';
import React, { useState, useEffect, useContext, useCallback } from 'react';
import { Message } from './Message/Message';
import { BattleScreen } from './BattleScreen/BattleScreen';
import { Form } from './Form/Form';
import { getNullData, getWaitingData } from './DataModel';
import { api_get, api_post } from './battle_api';

import '../../css/AdventureBattle.css';


export function TournamentBattle() {
    const [data, setData] = useState(getNullData());
    function updateData($data) {
        setData($prevData => Object.assign({}, $prevData, $data));
    }

    useEffect(() => {
        if(data.messages === null) {
            api_get('/tournament/start').then(function (response) {
                updateData(response.data);
            })
            .catch(function (error) {
                console.log('error', error);
            });
        }
    }, []);

    const [command, setCommand] = useState(null);
    function updateCommand(command, data) {
        updateData(getWaitingData());
        // -----------------------------------------------
        setCommand(command);
        if('selectPokemon' === command) {
            showImageOnScreen('ash', 'ash-support');
        } else if(
            (command === 'next' && data.turn === 'opponent') || ['attack', 'heal'].includes(command)
        ) {
            let className = [
                ['ash', 'ash-support'], 
                ['ash', 'ash-crazy'], 
                ['ash', 'ash-reverse'],
                ['cheerleaders1', 'cheerleader1'],
                ['cheerleaders2', 'cheerleader2'],
                ['crowd', 'crowd-move']
            ][Math.floor(Math.random() * Math.floor(6))];
            setTimeout(() => showImageOnScreen(className[0], className[1]), 800);
        }
        //-------------------------------------------------------
        api_post(data.url, data).then(function (response) {     
            console.log(response.data);
            updateData(response.data);
        })
        .catch(function (error) {
            console.log(error);
        });
    }

    const [showImage, setShowImage] = useState(false);
    const [imageObject, setImageObject] = useState(null);
    function showImageOnScreen(url, className) {
        setTimeout(() => setShowImage(false), 950);
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