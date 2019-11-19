export function getNullData() {
    return {
        form: null,
        opponent: null,
        player: null,
        messages: null,
        centerImageUrl: null,
        turn: null,
        pokeballCount: null,
        healingPotionCount: null
    };
}

export function getWaitingData() {
    return {
        messages: {
            messages: ['...'],
            textColor: 'text-white'
        },
        form: null
    };
}
export function getNoPokeballData() {
    return {
        messages: {
            messages: ['You don\'t have any pokeballs!'],
            textColor: 'battle-text-danger'
        }
    };
}

export function getNoHealingPotionData() {
    return {
        messages: {
            messages: ['You don\'t have any healing potion!'],
            textColor: 'battle-text-danger'
        }
    };
}

export function getPlayerAlreadyFullHPData() {
    return {
        messages: {
            messages: ['Your pokemon already has all its health points!'],
            textColor: 'battle-text-danger'
        }
    };
}

export function getOpponentHarmlessData() {
    return {
        messages: {
            messages: ['The pokemon is already harmless!'],
            textColor: 'battle-text-danger'
        }
    };
}