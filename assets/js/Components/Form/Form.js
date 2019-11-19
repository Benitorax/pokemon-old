import React, { useState, useEffect } from 'react';
import { Button } from './Button';
import { Select } from './Select';

export function Form(props) {
    if(props.form === null) return '';
    const [showForm, setShowForm] = useState(true);

    function tranferNewDataToParent(data) {
        props.onNewData(data);
        setShowForm(false);
    }

    function tranferCommandToParent(command) {
        props.onCommandExecuted(command);

    }
    
    useEffect(() => {
        setShowForm(true);
    }, [props.form]);

    let fields = props.form.map((field) => {
        if(field.type === 'button') {
            return <Button onNewData={tranferNewDataToParent} onCommandExecuted={tranferCommandToParent} key={field.name} info={field} />

        } else if(field.type === 'select') {
            return <Select onNewData={tranferNewDataToParent} onCommandExecuted={tranferCommandToParent} key={field.name} info={field} />
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