import React, { useState, useEffect } from 'react';
import {useTransition, animated } from 'react-spring';

import '../../css/AdventureBattle.css';

export function Message(props) {
    let messages = [];
    let textColor = "col-sm-12 p-3 mb-2 border border-secondary rounded-lg bg-battle-message";

    if(props.messages !== null) {
        messages.splice(0);
        props.messages.messages.forEach(message => {
            messages.push(message);
        });
        textColor += ' ' + props.messages.textColor;
    }

    let transitions = useTransition(messages, null, {
        from: { opacity: 0 },
        enter: { opacity: 1 },
        leave: { opacity: 0 },
    });

    return (
        <>
            <div className="row">
                <div className={textColor} style={{minHeight: '90px'}}>
                    {transitions.map(({ item, props, key }) =>
                        <animated.div dangerouslySetInnerHTML={{__html: item}} style={props} key={key} className="text-center"/>
                    )}
                </div>
            </div>
        </>
    );
}
