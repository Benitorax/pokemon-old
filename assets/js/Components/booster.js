import {getNoPokeballData, getNoHealingPotionData, getPlayerAlreadyFullHPData, getOpponentHarmlessData } from './DataModel';


export function isInvalidCommand(command, data) {
    if(command === 'throwPokeball' && parseInt(data.pokeballCount) === 0) {
        return getNoPokeballData();
    } else if(command === 'heal' && parseInt(data.healthPotionCount) === 0) {
        return getNoHealingPotionData();
    } else if(command === 'heal' && parseInt(data.player.healthPoint) === 100) {
        return getPlayerAlreadyFullHPData();
    } else if(command === 'attack' && parseInt(data.opponent.healthPoint) === 0) {
        return getOpponentHarmlessData();
    }

    return false;
}