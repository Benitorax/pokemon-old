import { Controller } from 'stimulus';
import React from 'react';
import ReactDOM from 'react-dom';
import { AdventureBattle } from '../js/Components/AdventureBattle';

/*
 * A Stimulus controller which generates a React component.
 */
export default class extends Controller {
    connect() {
        ReactDOM.render(
            <AdventureBattle/>,
            this.element
        );
    }
}
