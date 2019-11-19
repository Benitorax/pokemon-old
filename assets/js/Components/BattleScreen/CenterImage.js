import React, { useEffect, useState } from 'react';

export function CenterImage(props) {
    let command = props.command;
    const [urlImage, setUrlImage] = useState(null);
    const [className, setClassName] = useState(null);

    function setNewClassName(className) {
        setClassName('row '+className);
    }

    useEffect(() => {
        if(command === 'throwPokeball' && parseInt(props.pokeballCount) > 0) {
            setNewClassName('pokeball');
            setUrlImage('/images/pokeball.png')
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
            <div style={{ height: '96px' }}>
                <div className="row" style={{ maxHeight: '0px' }}>
                    { props.centerImageUrl === null ? null :
                        props.centerImageUrl.map( (centerImageUrl, index) =>
                            <img key={index} className="mx-auto mt-3" src={ centerImageUrl }/>
                        )
                    }
                </div>
            </div>
            { urlImage === null ? null :
                <div className={className} style={{ zIndex: 10, maxHeight: '0px'}}>
                    <img className="mx-auto" src={urlImage}/>
                </div>
            }
        </>
    );
}