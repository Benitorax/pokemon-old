import React, { useEffect, useState } from 'react';
import {useTransition, animated } from 'react-spring';

export function Opponent(props) {
    let opponents = []
    let opponent = props.opponent;
    opponents.push(opponent);

    let transitions = useTransition(opponents, {
        from: { opacity: 0.5, transform: 'translateX(50px)' },
        enter: { opacity: 1, transform: 'translateX(0)' },
        leave: { opacity: 0.5, display: 'none' },
        keys: item => item.key
    });  

    const baseClassName = 'mr-4';
    const [className, setClassName] = useState(baseClassName);

    useEffect(() => {
        setTimeout(() => setClassName(baseClassName + ' pokemon-bounce'), 1100);
    }, []);

    useEffect(() => {
        if(parseInt(opponent.healthPoint) === 0 ) {
            setClassName(baseClassName + ' pokemon-bounce pokemon-sleep')
        } else {
            setClassName(baseClassName + ' pokemon-bounce');
        }
    }, [opponent.healthPoint]);

    useEffect(() => {
        if(props.turn !== 'opponent') return;
        
        if(props.command === 'next') {
            setClassName(baseClassName + ' pokemon-attack-opponent');
        }

        setTimeout(() => setClassName(baseClassName + ' pokemon-bounce'), 1400);
    }, [props.command]);

    return (
        <>
            <div className="text-right"><strong>{ opponent.name }</strong></div>
            <div className="progress">
                <div className="progress-bar" role="progressbar" style={{width: opponent.healthPoint+"%"}} aria-valuenow={ opponent.healthPoint } aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            {transitions(({}, item) =>
                <animated.div style={props} key={item.name} className={className}><img className="float-right" src={item.spriteFrontUrl}/></animated.div>
            )}
        </>
    );
}