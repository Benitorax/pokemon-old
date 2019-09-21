<?php
namespace App\Handler;

use App\Form\Command\NextType;
use App\Handler\AdventureHandler;
use App\Form\Command\RestorePokemonsType;
use App\Form\Command\TournamentBattleType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Command\SelectPokemonForTournamentType;

class TournamentHandler extends AdventureHandler
{    
    public function handleRequest(Request $request)
    {
        $command = $request->request->keys()[0];
        $form = $this->commandManager->createFormByCommand($command);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handle($form);
        }
        dd('WHAT_TO_DO_IF_YOU_COME_HERE');
    }

    public function handle($form) 
    {
        $command = $form->getClickedButton()->getName();
        switch($command)
        {
            case 'selectPokemon':
                return $this->handleSelectPokemonForTournament($form);
            case 'attack':
                return $this->handleAttack();
            case 'heal':
                return $this->handleHeal();
            case 'next':
                return $this->handleNext();
            case 'restorePokemons':
                return $this->handleRestorePokemons();
        }
    }
    public function createBattle() 
    {
        $this->battleManager->createTournamentBattle();
    }

    public function handleSelectPokemonForTournament($data) 
    {
        $pokemonsCount = $this->battleManager->getPlayerTeam()->getPokemons()->count();

        if($pokemonsCount >= 3) {
            return $this->presentOpponent();
        }
        $this->battleManager->addFighterSelected($data->get('choicePokemon')->getData());
        
        $form = $this->commandManager->createCommandForm(SelectPokemonForTournamentType::class);
        $messages[] = "You have selected <strong>". $this->battleManager->getLastPlayerPokemon()->getName() ."</strong>!";
        $centerImageUrl = null;
        
        if($pokemonsCount == 0) { $messages[] = "Choose the 2nd pokemon to fight."; } 
        elseif($pokemonsCount == 1) { $messages[] = "Finally, choose the 3rd pokemon."; } 
        elseif($pokemonsCount == 2) { 
            $this->battleManager->startBattle();
            $messages[] = "<strong>". $this->battleManager->getOpponentTrainer()->getUsername() ."</strong> will be your opponent!";
            $form = $this->commandManager->createCommandForm(NextType::class);
            $centerImageUrl = $this->battleManager->getOpponentTrainer()->getEmail();
        }

        return [
            'messages' => $messages,
            'opponent' => null,
            'player' => null,
            'form' => $form,
            'centerImageUrl' => $centerImageUrl
        ];

    }

    public function presentOpponent()
    {
        $messages[] = "You have selected <strong>". $this->battleManager->getLastPlayerPokemon()->getName() ."</strong>!";
        $messages[] = "<strong>". $this->battleManager->getOpponentTrainer()->getUsername() ."</strong> will be your opponent!";

        return [
            'messages' => $messages,
            'opponent' => null,
            'player' => null,
            'form' => $this->commandManager->createCommandForm(NextType::class),
            'centerImageUrl' => $this->battleManager->getOpponentTrainer()->getEmail()
        ];
    }

    public function handleNext()
    {
        $battle = $this->battleManager->getCurrentBattle();
        if($this->isFighterSleeping()) {
            return $this->handleChangeFighter();
        }
        // Preventing the user to refresh page to attack infinitely
        if($battle->getTurn() == 'opponent') 
        {   
            return $this->handleOpponentTurn();
        }
        
        $messages[] = "<strong>".$this->battleManager->getOpponentTrainer()->getUsername()."</strong> invokes <strong>". $this->battleManager->getOpponentFighter()->getName() ."</strong>!";

        return [
            'messages' => $messages,
            'opponent' => $this->battleManager->getOpponentTeam(),
            'player' => $this->battleManager->getPlayerTeam(),
            'form' => $this->commandManager->createCommandForm(TournamentBattleType::class),
        ];

    }

    public function handleOpponentTurn() {
        /** @var BattleManager $this->battleManager */
        $damage = $this->battleManager->manageDamagePlayerFighter();
        $battle = $this->battleManager->getCurrentBattle();
        $opponentFighter = $battle->getOpponentTeam()->getCurrentFighter();
        $playerFighter = $battle->getPlayerTeam()->getCurrentFighter(); 
        $form = $this->commandManager->createCommandForm(TournamentBattleType::class);

        if($playerFighter->getIsSleep()) {
            $form = $this->commandManager->createCommandForm(NextType::class);
            $messages[] = "<strong>". $opponentFighter->getName() ."</strong> has knocked <strong>". 
                            $playerFighter->getName() ."</strong> out (-".$damage." HP).";
            $form = $this->commandManager->createCommandForm(NextType::class);
        } else {
            $messages[] = "<strong>". $opponentFighter->getName() ."</strong> attacks <strong>". $playerFighter->getName() ."</strong>"; 
            $messages[] = "It inflicts ".$damage." points of damage.";
        }

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $form
        ];
    }

    public function handleHeal() {
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = $battle->getOpponentTeam();
        $playerTeam = $battle->getPlayerTeam();

        if($this->battleManager->getPlayerTeam()->getHealCount() >= 3) {
            $messages[] = "You have already used your 3rd and last health potion!";
        } else {
            $hpRange = $this->battleManager->manageHealPlayerFighter();
            if($hpRange) {
                $messages[] = "<strong>".$playerTeam->getCurrentFighter()->getName() ."</strong> has been healed (+".$hpRange."HP)!";
            } else {
                $messages[] = "You don't have any health potions!";
            }
        }

        return [
            'messages' => $messages,
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $this->commandManager->createCommandForm(TournamentBattleType::class)
        ];
    }

    public function isFighterSleeping() {
        return $this->battleManager->getPlayerFighter()->getIsSleep() ||
               $this->battleManager->getOpponentFighter()->getIsSleep();
    }

    public function handleChangeFighter() {
        if($this->battleManager->getOpponentFighter()->getIsSleep()) {
            $isChanged = $this->battleManager->manageChangeFighterOfTeam($this->battleManager->getOpponentTeam());
            if($isChanged) {
                $messages[] = "<strong>". $this->battleManager->getOpponentTrainer()->getUsername() ."</strong> invokes <strong>". $this->battleManager->getOpponentFighter()->getName() ."</strong>"; 
            }
        }

        if($this->battleManager->getPlayerFighter()->getIsSleep()) {
            $isChanged = $this->battleManager->manageChangeFighterOfTeam($this->battleManager->getPlayerTeam());
            if($isChanged) {
                $messages[] = "You summon <strong>". $this->battleManager->getPlayerFighter()->getName() ."</strong>"; 
            }
        }
        $form = $this->commandManager->createCommandForm(TournamentBattleType::class);

        if($this->battleManager->getCurrentBattle()->getIsEnd()) {
            return $this->handleEndBattle();
        }

        return [
            'messages' => $messages,
            'opponent' =>  $this->battleManager->getOpponentTeam(),
            'player' => $this->battleManager->getPlayerTeam(),
            'form' => $form
        ];    
    }

    public function handleRestorePokemons() {
        $messages[] = "The infirmary service is free for participants of the tournament.";
        $messages[] = "Your pokemons are now in good shape.";
        $messages[] = 'Select your 1st pokemon if you want to go on.';
        $this->battleManager->restorePlayerPokemons();
        $this->clear();
        $this->createBattle();
        return [
            'messages' => $messages,
            'opponent' => null,
            'player' => null,
            'form' => $this->commandManager->createCommandForm(SelectPokemonForTournamentType::class)
        ];    
    }

    public function handleEndBattle()
    {
        $user = $this->battleManager->getUser();
        if($this->battleManager->getPlayerTeam()->getIsVictorious()) {
            $user->increaseConsecutiveWin();
            $datas = $this->battleManager->manageLevelUpForTournament();
            if(is_int($user->getConsecutiveWin() / 3)) {
                $user->increasePokedollar(500);
                $user->increaseChampionCount();
                $messages[] = "Congrats! You won the battle and 500$!";
            } else {
                $user->increasePokedollar(300);
                $messages[] = "Congrats! You won the battle and 300$!";
            }

            foreach($datas as $data) {
                if($data['hasEvolved']) {
                    $messages[] = "<strong>".$data['name']."</strong> evolves to ".$data['newName']." (level: ".$data['newLevel'].".";
                } elseif($data['hasLeveledUp']) {
                    $messages[] = "<strong>".$data['name']."</strong> levels up to ".$data['newLevel']." (+".$data['increasedLevel'].").";
                }
            }
        } else {
            $user->increasePokedollar(100);
            $user->resetConsecutiveWin();
            $messages[] = "You have lost!";
            $messages[] = "You earn 100$ thanks to the battle!";
        }
        $this->manager->flush();

        return [
            'messages' => $messages,
            'opponent' => null,
            'player' => null,
            'form' => $this->commandManager->createCommandForm(RestorePokemonsType::class)
        ];  
    } 
}