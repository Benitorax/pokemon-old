export function getNullData() {
    return {
        form: null,
        opponent: null,
        player: null,
        messages: null,
        centerImageUrl: null,
        turn: null,
        pokeballCount: null,
        healthPotionCount: null
    };
}

export function getWaitingData() {
    return {
        messages: {
            messages: ['...'],
            textColor: 'text-white'
        }
    };
}