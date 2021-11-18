<?php

namespace App\Handler;

use App\Entity\Battle;
use App\Entity\Pokemon;
use App\Manager\BattleManager;
use App\Handler\AdventureHandler;
use App\Manager\BattleFormManager;

class TournamentHandler extends AdventureHandler
{
    public function createBattle(): Battle
    {
        return $this->battleManager->createTournamentBattle();
    }

    public function handleSelectPokemon(Pokemon $pokemon): array
    {
        $pokemonsCount = $this->battleManager->getPlayerTeam()->getPokemons()->count();

        if ($pokemonsCount >= 3) {
            return $this->presentOpponent();
        }

        $this->battleManager->addFighterSelected($pokemon);
        $form = [$this->battleFormManager->createSelectPokemonFieldForTournament()];
        $messages[] = 'You have selected <strong>'
            . $this->battleManager->getLastPlayerPokemon()->getName()
            . '</strong>!'
        ;
        $centerImageUrl = null;

        if ($pokemonsCount == 0) {
            $messages[] = 'Choose the 1st substitute.';
        } elseif ($pokemonsCount == 1) {
            $messages[] = 'Finally, choose the 2nd substitute.';
        } elseif ($pokemonsCount == 2) {
            $this->battleManager->startBattle();
            $messages[] = '<strong>' . $this->battleManager->getOpponentTrainer()->getUsername()
                . '</strong> will be your opponent!';
            $form = [$this->battleFormManager->createNextButton(BattleFormManager::TOURNAMENT_MODE)];
            $centerImageUrl = [$this->battleManager->getOpponentTrainer()->getEmail()];
        }

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' => null,
            'player' => null,
            'form' => $form,
            'centerImageUrl' => $centerImageUrl
        ];
    }

    public function handleAttack(): array
    {
        $turn = 'player';
        $damage = $this->battleManager->manageAttackOpponent();

        if ($this->battleManager->getOpponentFighter()->getIsSleep()) {
            $messages[] = '<strong>' . $this->battleManager->getPlayerFighter()->getName()
                . '</strong> attacks <strong>' . $this->battleManager->getOpponentFighter()->getName()
                . '</strong> with ' . $damage . ' points of damage!';
            $messages[] = '<strong>' . $this->battleManager->getOpponentFighter()->getName()
                . '</strong> has fainted.';
            $textColor = 'battle-text-success';
        } else {
            $messages[] = '<strong>' . $this->battleManager->getPlayerFighter()->getName()
                . '</strong> attacks <strong>' . $this->battleManager->getOpponentFighter()->getName()
                . '</strong>!';
            $messages[] = "It inflicts " . $damage . " points of damage.";
            $turn = 'opponent';
        }

        $battle = $this->battleManager->getCurrentBattle();

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => isset($textColor) ? $textColor : 'text-white'
            ],
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => [$this->battleFormManager->createNextButton(BattleFormManager::TOURNAMENT_MODE)],
            'turn' => $turn
        ];
    }

    public function presentOpponent(): array
    {
        $messages[] = 'You have selected <strong>' . $this->battleManager->getLastPlayerPokemon()->getName()
            . '</strong>!';
        $messages[] = '<strong>' . $this->battleManager->getOpponentTrainer()->getUsername()
            . '</strong> will be your opponent!';

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' => null,
            'player' => null,
            'form' => [$this->battleFormManager->createNextButton(BattleFormManager::TOURNAMENT_MODE)],
            'centerImageUrl' => [$this->battleManager->getOpponentTrainer()->getEmail()]
        ];
    }

    public function handleNext(): array
    {
        $battle = $this->battleManager->getCurrentBattle();

        if ($this->isFighterSleeping()) {
            return $this->handleChangeFighter();
        }

        // Preventing the user to refresh page to attack infinitely
        if ($battle->getTurn() == 'opponent') {
            return $this->handleOpponentTurn();
        }

        $messages[] = '<strong>' . $this->battleManager->getOpponentTrainer()->getUsername()
            . '</strong> invokes <strong>' . $this->battleManager->getOpponentFighter()->getName()
            . '</strong>!';

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' => $this->battleManager->getOpponentTeam(),
            'player' => $this->battleManager->getPlayerTeam(),
            'form' => $this->battleFormManager->createTournamentButtons(),
            'centerImageUrl' => null
        ];
    }

    public function handleOpponentTurn(): array
    {
        $damage = $this->battleManager->manageDamagePlayerFighter();
        $battle = $this->battleManager->getCurrentBattle();
        $opponentFighter = $battle->getOpponentTeam()->getCurrentFighter();
        $playerFighter = $battle->getPlayerTeam()->getCurrentFighter();
        $form = $this->battleFormManager->createTournamentButtons();

        if ($playerFighter->getIsSleep()) {
            $messages[] = '<strong>' . $opponentFighter->getName() . '</strong> has knocked <strong>' .
                            $playerFighter->getName() . '</strong> out (-' . $damage . ' HP).';
            $form = [$this->battleFormManager->createNextButton(BattleFormManager::TOURNAMENT_MODE)];
        } else {
            $messages[] = '<strong>' . $opponentFighter->getName() . '</strong> attacks <strong>'
                . $playerFighter->getName() . '</strong>';
            $messages[] = 'It inflicts ' . $damage . ' points of damage.';
        }

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'battle-text-danger'
            ],
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $form
        ];
    }

    public function handleHeal(): array
    {
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = $battle->getOpponentTeam();
        $playerTeam = $battle->getPlayerTeam();
        $textColor = 'battle-text-danger';
        $healCount = $this->battleManager->getPlayerTeam()->getHealCount();

        if ($healCount >= 3) {
            $messages[] = 'You have already used your 3rd and last healing potion!';
        } else {
            $result = $this->battleManager->manageHealPlayerFighter();
            if ($result === BattleManager::POKEMON_HP_FULL) {
                $messages[] = 'Your pokemon already has all its health points!';
            } elseif ($result === BattleManager::NO_HP_POTION) {
                $messages[] = 'You don\'t have any healing potions!';
                $textColor = 'battle-text-danger';
            } else {
                $healCount += 1;
                $messages[] = '<strong>' . $playerTeam->getCurrentFighter()->getName()
                    . '</strong> has been healed (+' . $result . 'HP)!';
                $messages[] = $healCount . ' potion' . ($healCount > 1 ? 's' : '') . ' used.';
                $textColor = 'battle-text-info';
            }
        }

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => $textColor
            ],
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $this->battleFormManager->createTournamentButtons(),
        ];
    }

    public function isFighterSleeping(): bool
    {
        return $this->battleManager->getPlayerFighter()->getIsSleep() ||
               $this->battleManager->getOpponentFighter()->getIsSleep();
    }

    public function handleChangeFighter(): array
    {
        $messages = [];

        if ($this->battleManager->getOpponentFighter()->getIsSleep()) {
            $isChanged = $this->battleManager->manageChangeFighterOfTeam($this->battleManager->getOpponentTeam());

            if ($isChanged) {
                $messages[] = '<strong>' . $this->battleManager->getOpponentTrainer()->getUsername()
                    . '</strong> invokes <strong>' . $this->battleManager->getOpponentFighter()->getName()
                    . '</strong>';
            }
        }

        if ($this->battleManager->getPlayerFighter()->getIsSleep()) {
            $isChanged = $this->battleManager->manageChangeFighterOfTeam($this->battleManager->getPlayerTeam());

            if ($isChanged) {
                $messages[] = 'You summon <strong>' . $this->battleManager->getPlayerFighter()->getName() . '</strong>';
            }
        }

        if ($this->battleManager->getCurrentBattle()->getIsEnd()) {
            return $this->handleEndBattle();
        }

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' =>  $this->battleManager->getOpponentTeam(),
            'player' => $this->battleManager->getPlayerTeam(),
            'form' => $this->battleFormManager->createTournamentButtons(),
            'centerImageUrl' => null
        ];
    }

    public function handleRestorePokemons(): array
    {
        $messages[] = 'The infirmary service is free for participants of the tournament.';
        $messages[] = 'Your pokemons are now in good shape.';
        $messages[] = 'Select your 1st pokemon if you want to go on.';

        $this->battleManager->restorePlayerPokemons();
        $this->clear();
        $this->createBattle();

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' => null,
            'player' => null,
            'form' => [$this->battleFormManager->createSelectPokemonFieldForTournament()]
        ];
    }

    public function handleEndBattle(): array
    {
        $user = $this->battleManager->getUser();
        $centerImageUrlArray = [];

        if ($this->battleManager->getPlayerTeam()->getIsVictorious()) {
            $user->increaseConsecutiveWin();
            $datas = $this->battleManager->manageLevelUpForTournament();

            if (0 === ($user->getConsecutiveWin() % 3)) {
                $user->increasePokedollar(700);
                $user->increaseChampionCount();
                $badgeNumber = $this->getOrdinalNumberFromBadgesCount($user->getChampionCount());
                $centerImageUrlArray[] = '/images/badge' . $badgeNumber . '.png';
                $messages[] = 'Congrats! You won the final and earn 700$!';
            } else {
                $user->increasePokedollar(300);
                $messages[] = 'Congrats! You won the battle and earn 300$!';
            }

            foreach ($datas as $data) {
                if ($data['hasEvolved']) {
                    $messages[] = '<strong>' . $data['name']
                        . '</strong> evolves to <strong>' . $data['newName']
                        . '</strong> (level: ' . $data['newLevel'] . ').';
                    $centerImageUrlArray[] = $data['SpriteFrontUrl'];
                } elseif ($data['hasLeveledUp']) {
                    $messages[] = '<strong>' . $data['name']
                        . '</strong> levels up to <strong>' . $data['newLevel']
                        . '</strong> (+' . $data['increasedLevel'] . ').';
                }
            }
        } else {
            $user->increasePokedollar(100);
            $user->resetConsecutiveWin();
            $messages[] = 'You have lost!';
            $messages[] = 'You earn 100$ thanks to the battle!';
        }

        $this->manager->flush();

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' => null,
            'player' => null,
            'form' => [$this->battleFormManager->createRestorePokemonsButton()],
            'centerImageUrl' => $centerImageUrlArray
        ];
    }

    /** The game has 8 different badges, so the 9th badge corresponds to the 1st, and the 10th corresponds to the 2nd...  */
    public function getOrdinalNumberFromBadgesCount(int $number): int
    {
        if ($number < 9) {
            return $number;
        }

        $number %= 8;

        if ($number === 0) {
            $number = 8;
        }

        return $number;
    }
}
