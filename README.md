# Pokemon Application

## Database
<p>Update the config in .env file for your database.</p>

<p>Create the database<br>
<code>php bin/console doctrine:database:create</code><br>
<code>php bin/console doctrine:schema:create</code><br>
<code>php bin/console doctrine:schema:update --force</code>
<br><br>
To launch the server<br>
<code>composer require --dev symfony/web-server-bundle</code><br>
<code>php bin/console server:start</code><br>

## Application
<p>You can:</p>
<ul>
  <li>create an account user</li>
  <li>select a pokemon when you register</li>
  <li>reset your password if you don't remember it</li>
  <li>log in</li>
  <li>modify your password</li>
  <li>to capture pokemons</li>
  <li>to buy items (pokeballs, health potions)</li>
  <li>to participate in tournament</li>
  <li>to delete your account</li>
</ul>
<br>
<p>It send an email:</p>
<ul>
  <li>to activate your account/to confirm your email address</li>
  <li>to reset your password if forgotten</li>
  <li>to confirm that your password has been changed</li>
</ul>
