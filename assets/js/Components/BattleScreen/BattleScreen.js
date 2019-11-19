import React, { useEffect, useState } from 'react';
import { CenterImage } from './CenterImage';
import { Opponent } from './Opponent';
import { Player } from './Player';

export function BattleScreen(props) {
    let healthPoint = props.player !== null ? props.player.healthPoint : null;

    useEffect(() => {
        let screenWidth = document.getElementById('screen').offsetWidth;
        let screenHeight = document.getElementById('screen').offsetHeight;
        let screen = document.documentElement;
        screen.style.setProperty('--width', screenWidth + 'px');
        screen.style.setProperty('--height', screenHeight + 'px');
        screen.style.setProperty('--half-width', screenWidth/2 + 'px');
        screen.style.setProperty('--half-height', screenHeight/2 + 'px');
    }, []);


    return (
        <div className="row">
            <div id="screen" style={{minHeight: '300px'}} className="col-sm-12 mb-2 border border-secondary rounded-lg">
                <div className="mt-2">
                    { props.opponent !== null ? <Opponent turn={props.turn} command={props.command} opponent={props.opponent}/> : null }
                </div>
                <CenterImage 
                    healthPoint = {healthPoint}
                    pokeballCount={props.pokeballCount} 
                    healthPotionCount={props.healthPotionCount} 
                    centerImageUrl={props.centerImageUrl} 
                    command={props.command}
                />
                <div className="mb-3">
                    { props.player !== null ? <Player turn={props.turn} command={props.command} player={props.player}/> : null }
                </div>
            </div>
        </div>
    );
}

