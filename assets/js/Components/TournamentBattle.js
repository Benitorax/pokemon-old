import { render } from 'react-dom';
import React, { useState } from 'react';
import { Battle } from './Battle';
import { getNullImageObject } from './DataModel';

import '../../styles/AdventureBattle.css';


export function TournamentBattle() {
    const [startUrl, setStartUrl] = useState('/tournament/start');
    const [showImage, setShowImage] = useState(false);
    const [imageObject, setImageObject] = useState(getNullImageObject());

    function showImageOnScreen(url, className) {
        setTimeout(() => setShowImage(false), 950);
        setImageObject({
            url: '/images/' + url + '.png',
            className: 'row ' + className
        });
        setShowImage(true);
    }

    function checkExecutedCommand(command, turn) {
        if ('selectPokemon' === command) {
            showImageOnScreen('ash', 'ash-support');
        } else if (
            (
                (command === 'next' && turn === 'opponent') || ['attack', 'heal'].includes(command)
            ) && Math.random() > 0.70
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
    }

    return <Battle startUrl = { startUrl }
    imageObject = { imageObject }
    showImage = { showImage }
    onExecutedCommand = { checkExecutedCommand }
    />;
}

(function() {
    const battleNode = document.getElementById('r-battle');
    render( <
        TournamentBattle / > ,
        battleNode
    );
})();