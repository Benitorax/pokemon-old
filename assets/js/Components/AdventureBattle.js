import { render } from 'react-dom';
import React, { useState, useEffect, useContext, useCallback } from 'react';
import { Message } from './Message';
import { BattleScreen } from './BattleScreen';
import { Form } from './Form';
import { getDataModel } from './DataModel';

import '../../css/AdventureBattle.css';


export function AdventureBattle() {
    const [data, setData] = useState(getDataModel());
    function updateData($data) {
        setData($prevData => Object.assign({}, $prevData, $data));
    }

    useEffect(() => {
        if(data.messages === null) {
            axios.get('/adventure/start')
            .then(function (response) {
                console.log('response from /adventure/start', response.data);
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
        if(['travel', 'leave'].includes(command)) {
            showAshOnScreen('ash-support');
        }
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