import { Controller } from 'stimulus';
import React from 'react';
import ReactDOM from 'react-dom';
import { TournamentBattle } from '../js/Components/TournamentBattle';

/*
 * A Stimulus controller which generates a React component.
 */
export default class extends Controller {
    connect() {
        ReactDOM.render(
            <TournamentBattle/>,
            this.element
        );
    }
}
