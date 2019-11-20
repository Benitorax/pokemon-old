import { render } from 'react-dom';
import React, { useState, useEffect } from 'react';
import { Message } from './Message/Message';
import { BattleScreen } from './BattleScreen/BattleScreen';
import { Form } from './Form/Form';
import { getNullData, getWaitingData, getServerErrorData } from './DataModel';
import { api_get, api_post } from './battle_api';
import { isInvalidCommand } from './booster';

export function Battle(props) {
    const [data, setData] = useState(null);
    function updateData($data) {
        setData($prevData => Object.assign({}, $prevData, $data));
    }

    useEffect(() => {
        api_get(props.startUrl).then(function (response) {
            updateData(response.data);
        })
        .catch(function (error) {
            console.log('error', error);
            updateData(getServerErrorData());
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
        setCommand(command);
        props.onExecutedCommand(command, data.turn);

        api_post(dataForApi.url, dataForApi).then(function (response) {     
            console.log(response.data);
            updateData(response.data);
        })
        .catch(function (error) {
            console.log('error', error);
            updateData(getServerErrorData());
        });
    }

    return (
        <div className="col-sm-10 col-md-8 col-lg-6 col-xl-5 mt-3">
            <Message messages={data.messages} />
            <BattleScreen 
                pokeballCount={data.pokeballCount} 
                healingPotionCount={data.healingPotionCount} 
                turn={data.turn} 
                opponent={data.opponent} 
                player={data.player} 
                command={command} 
                centerImageUrl={data.centerImageUrl}
            />
            { data.form !== null ? <Form turn={data.turn} onCommandExecuted={updateCommand} onNewData={updateData} form={data.form}/> : null }
            { props.showImage === false ? null : <div className={props.imageObject.className} style={{ maxHeight: '0px' }}><img className="mx-auto" src={props.imageObject.url}/></div> }
        </div>
    );
}