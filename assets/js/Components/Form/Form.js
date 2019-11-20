import React, { useState, useEffect } from 'react';
import { Button } from './Button';
import { Select } from './Select';

export function Form(props) {
    if(props.form === null) return '';
    const [showForm, setShowForm] = useState(true);

    function tranferCommandToParent(command, data) {
        props.onCommandExecuted(command, data);
    }
    
    useEffect(() => {
        setShowForm(true);
    }, [props.form]);

    let fields = props.form.map((field) => {
        if(field.type === 'button') {
            return <Button onCommandExecuted={tranferCommandToParent} key={field.name} info={field} />

        } else if(field.type === 'select') {
            return <Select onCommandExecuted={tranferCommandToParent} key={field.name} info={field} />
        }
    });

    return (
        <>
            { showForm === false ? null :
                <form name="adventure_battle" method="post" className="adventure-form form-inline justify-content-around">
                    { fields }
                </form>
            }
        </>
    );
}