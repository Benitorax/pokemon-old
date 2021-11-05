import React, { useState, useEffect } from 'react';
import {useTransition, animated } from 'react-spring';
import uuidv4 from 'uuid/v4';

export function Message(props) {
    const [messages, setMessages] = useState([]);
    const [textColor, setTextColor] = useState([]);

    useEffect(() => {
        if(props.messages !== null) {
            let newMessages = [];
            let message = props.messages.messages.join('<br/>');
            newMessages.push({
                id: uuidv4(),
                message: message
            });
            setMessages(newMessages);
            setNewTextColor(props.messages.textColor);
        }
    }, [props.messages]);

    function setNewTextColor(textColor) {
        let baseTextColor = "col-sm-12 p-3 mb-2 border border-secondary rounded-lg bg-battle-message";
        setTextColor(baseTextColor + ' ' + textColor);
    }

    const transitions = useTransition(messages, {
        from: { opacity: 0 },
        enter: { opacity: 1 },
        leave: { opacity: 0, display: 'none' },
        keys: item => item.key
    });

    return (
        <>
            <div className="row">
                <div className={textColor} style={{minHeight: '90px'}}>
                    { props.messages.length === 0 ? null :
                        transitions(({}, item) =>
                            {
                                return <animated.div dangerouslySetInnerHTML={{__html: item.message}} style={props} key={item.id} className="text-center"/>;
                            }
                        )
                    }
                </div>
            </div>
        </>
    );
}
