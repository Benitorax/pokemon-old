import { render } from 'react-dom';
import React, { useState } from 'react';
import { Battle } from './Battle';
import { getNullImageObject } from './DataModel';

import '../../styles/AdventureBattle.css';


export function AdventureBattle() {
    const [startUrl, setStartUrl] = useState('/adventure/start');
    const [showImage, setShowImage] = useState(false);
    const [imageObject, setImageObject] = useState(getNullImageObject());

    function showImageOnScreen(url, className) {
        setShowImage(true);

        setTimeout(() => setShowImage(false), 1000);
        setImageObject({
            url: '/images/' + url + '.png',
            className: 'row ' + className
        });
    }

    function checkExecutedCommand(command, turn) {
        if (['travel', 'leave'].includes(command)) {
            showImageOnScreen('ash', 'ash-support');
        }
    }

    return <Battle startUrl = { startUrl }
    imageObject = { imageObject }
    showImage = { showImage }
    onExecutedCommand = { checkExecutedCommand }
    />;
}
