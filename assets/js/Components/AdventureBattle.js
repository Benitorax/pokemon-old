import { render } from 'react-dom';
import React, { useState, useEffect } from 'react';
import { Message } from './Message/Message';
import { BattleScreen } from './BattleScreen/BattleScreen';
import { Form } from './Form/Form';
import { getNullData, getWaitingData } from './DataModel';
import { api_get, api_post } from './battle_api';
import { isInvalidCommand } from './booster';

import '../../css/AdventureBattle.css';


export function AdventureBattle() {
    const [data, setData] = useState(getNullData());
    function updateData($data) {
        setData($prevData => Object.assign({}, $prevData, $data));
    }

    useEffect(() => {
        api_get('/adventure/start').then(function (response) {
            updateData(response.data);
        })
        .catch(function (error) {
            console.log('error', error);
        });
    }, []);

    const [command, setCommand] = useState(null);
    function updateCommand(command, dataForApi) {
        let result = isInvalidCommand(command, data);
        if(result) {
            updateData(result);
            return;
        }
        updateData(getWaitingData());
        //-------------------------------------
        setCommand(command);
        if(['travel', 'leave'].includes(command)) {
            showAshOnScreen('ash-support');
        }
        // ------------------------------------
        api_post(dataForApi.url, dataForApi).then(function (response) {     
            console.log(response.data);
            updateData(response.data);
        })
        .catch(function (error) {
            console.log(error);
        });
    }

    const [showAsh, setShowAsh] = useState(false);
    const [classNameAsh, setClassNameAsh] = useState(null);
    function showAshOnScreen(className) {
        setShowAsh(true);
        setTimeout(() => setShowAsh(false), 1000);
        setClassNameAsh('row '+className)
    }

    return (
        <div className="col-sm-10 col-md-8 col-lg-6 col-xl-5 mt-3">
            <Message messages={data.messages}/>
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
            { showAsh === false ? null : <div className={classNameAsh} style={{ maxHeight: '0px' }}><img className="mx-auto" src="/images/ash.png"/></div> }
        </div>
    );
}

const battleNode = document.getElementById('r-battle');
render(
    <AdventureBattle/>, 
    battleNode
);