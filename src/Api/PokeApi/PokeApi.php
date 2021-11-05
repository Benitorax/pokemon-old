<?php

namespace App\Api\PokeApi;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PokeApi
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetch($endpoint)
    {
        $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/'.$endpoint);
        $data = $response->getContent();
        
        return $data = json_decode($data, true);
    }

    public function getPokemonsIdFromHabitat($habitatId)
    {
        $data = $this->fetch('pokemon-habitat/'.$habitatId);
        $pokemonsData = $data['pokemon_species'];
        $listId = [];

        foreach($pokemonsData as $pokemonData) 
        {
            $id = $this->getIdFromUrl($pokemonData['url']);
            
            if($id < 152)
            {
                $listId[] = $id;
            } 
            else 
            {
                break;
            }
        }

        return $listId;
    }

    public function getIdFromUrl($url)
    {
        $url = rtrim($url,"/");
        $length = strlen($url);
        $id = "";

        for($i = $length-1 ; $i > strlen('https://pokeapi.co/api/v2/') ; $i--)
        {
            if($url[$i] !== "/")
            {
                $id = $url[$i] . $id;
            } 
            else 
            {
                break;
            }
        }

        return intval($id);
    }
}