import React, { useEffect, useState } from 'react';
import {useTransition, animated } from 'react-spring';

import '../../css/AdventureBattle.css';

function Opponent(props) {
    let opponents = []
    let opponent = props.opponent;
    opponents.push(opponent);

    let transitions = useTransition(opponents, null, {
        from: { opacity: 0.5, transform: 'translateX(50px)' },
        enter: { opacity: 1, transform: 'translateX(0)' },
        leave: { opacity: 0.5, filter: 'brightness(0%)' },
    });  

    const baseClassName = 'mr-4';
    const [className, setClassName] = useState(baseClassName);

    useEffect(() => {
        setTimeout(() => { 
            setClassName(baseClassName + ' pokemon-bounce');
        }, 1100);
    }, []);

    useEffect(() => {
        if(parseInt(opponent.healthPoint) === 0 ) {
            setClassName(baseClassName + ' pokemon-bounce pokemon-sleep')
        }
    }, [opponent.healthPoint]);

    useEffect(() => {
        if(props.turn !== 'opponent') return;
        
        if(props.command === 'next') {
            setClassName(baseClassName + ' pokemon-attack-opponent');
        }

        setTimeout(() => { 
            setClassName(baseClassName + ' pokemon-bounce');
        }, 1400);
    }, [props.command]);

    return (
        <>
            <div className="text-right"><strong>{ opponent.name }</strong></div>
            <div className="progress">
                <div className="progress-bar" role="progressbar" style={{width: opponent.healthPoint+"%"}} aria-valuenow={ opponent.healthPoint } aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            {transitions.map(({ item, props, key }) =>
                <animated.div style={props} key={key} className={className}><img className="float-right" src={item.spriteFrontUrl}/></animated.div>
            )}
        </>
    );
}

function Player(props) {
    let players = []
    let player = props.player;
    players.push(player);

    let transitions = useTransition(players, null, {
        from: { opacity: 0, transform: 'translateX(-50px)' },
        enter: { opacity: 1, transform: 'translateX(0)' },
        leave: { opacity: 0, filter: 'brightness(0%)' },
    });   

    const baseClassName = 'mr-4';
    const [className, setClassName] = useState(baseClassName);

    useEffect(() => {
        setTimeout(() => { 
            setClassName(baseClassName + ' pokemon-bounce');
        }, 1100);
    }, []);

    useEffect(() => {
        if(props.command === 'attack') {
            setClassName(baseClassName + ' pokemon-attack-player');
        }
        setTimeout(() => { 
            setClassName(baseClassName + ' pokemon-bounce');
        }, 1400);
    }, [props.command]);

    return (
        <>
            {transitions.map(({ item, props, key }) =>
                <animated.div style={props} key={key} className={className}><img src={ item.spriteBackUrl }/></animated.div>
            )}
            <div className="text-left"><strong>{ player.name }</strong></div>
            <div className="progress">
                <div className="progress-bar" role="progressbar" style={{width: player.healthPoint +"%"}} aria-valuenow={ player.healthPoint } aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </>
    );
}

function CenterImage(props) {
    let command = props.command;
    const [urlImage, setUrlImage] = useState(null);
    const [className, setClassName] = useState(null);

    function setNewClassName(className) {
        setClassName('row '+className);
    }

    useEffect(() => {
        if(command === 'throwPokeball' && parseInt(props.pokeballCount) > 0) {
            setNewClassName('pokeball');
            setUrlImage('/images/pokeball3.png')
            setTimeout(() => {
                setUrlImage(null)
                setClassName(null);
            }, 1000);
        }

        if(command === 'heal' && parseInt(props.healthPotionCount) > 0 && parseInt(props.healthPoint) < 100) {
            setNewClassName('health-potion');
            setUrlImage('/images/health-potion.png')
            setTimeout(() => {
                setUrlImage(null)
                setClassName(null);
            }, 1000);
        }
    }, [command]);
    
    return (
        <>
            { urlImage === null ? null :
                <div className={className} style={{ zIndex: 10, maxHeight: '0px'}}>
                    <img className="mx-auto" src={urlImage}/>
                </div>
            }
            { props.centerImageUrl === null ? null :
                <div className="row">
                    <img className="mx-auto" src={ props.centerImageUrl }/>
                </div>
            }
        </>
    );
}

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
                </div><br/><br/><br/><br/>
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

