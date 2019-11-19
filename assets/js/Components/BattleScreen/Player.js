import React, { useEffect, useState } from 'react';
import {useTransition, animated } from 'react-spring';

export function Player(props) {
    let players = []
    let player = props.player;
    players.push(player);

    let transitions = useTransition(players, null, {
        from: { opacity: 0, transform: 'translateX(-50px)' },
        enter: { opacity: 1, transform: 'translateX(0)' },
        leave: { opacity: 0, display: 'none' },
    });   

    const baseClassName = 'mr-4';
    const [className, setClassName] = useState(baseClassName);

    useEffect(() => {
        setTimeout(() => setClassName(baseClassName + ' pokemon-bounce'), 1100);
    }, []);

    useEffect(() => {
        if(props.command === 'attack') {
            setClassName(baseClassName + ' pokemon-attack-player');
        }
        setTimeout(() => setClassName(baseClassName + ' pokemon-bounce'), 1400);
    }, [props.command]);

    useEffect(() => {
        if(parseInt(player.healthPoint) === 0 ) {
            setClassName(baseClassName + ' pokemon-bounce pokemon-sleep')
        } else {
            setClassName(baseClassName + ' pokemon-bounce');
        }
    }, [player.healthPoint]);

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
