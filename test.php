<?php 
	require_once 'core/required/session.php';

	$Type = 'Shiny';
	$Pokedex_ID = 359;
	$Pokemon_Forme = '';

	$Sprite_DS = DOMAIN_SPRITES . "/Pokemon/Sprites/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
	if ( !file_exists($Sprite_DS) )
		$Sprite_DS = DOMAIN_SPRITES . "/Pokemon/Sprites/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";

	$Sprite_DR = DOMAIN_ROOT . "/images/Pokemon/Sprites/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
	if ( !file_exists($Sprite_DR) )
		$Sprite_DR = DOMAIN_ROOT . "/images/Pokemon/Sprites/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";

	$Sprite_SR = SERVER_ROOT . "/images/Pokemon/Sprites/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
	if ( !file_exists($Sprite_SR) )
		$Sprite_SR = SERVER_ROOT . "/images/Pokemon/Sprites/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";

	$Sprite_SS = SERVER_SPRITES . "/Pokemon/Icons/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
	if ( !file_exists($Sprite_SS) )
		$Sprite_SS = SERVER_SPRITES . "/Pokemon/Icons/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";

	$Icon_DS = DOMAIN_SPRITES . "/Pokemon/Icons/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
	if ( !file_exists($Icon_DS) )
		$Icon_DS = DOMAIN_SPRITES . "/Pokemon/Icons/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";

	$Icon_DR = DOMAIN_ROOT . "/images/Pokemon/Icons/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
	if ( !file_exists($Icon_DR) )
		$Icon_DR = DOMAIN_ROOT . "/images/Pokemon/Icons/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";

	$Icon_SR = SERVER_ROOT . "/images/Pokemon/Icons/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
	if ( !file_exists($Icon_SR) )
		$Icon_SR = SERVER_ROOT . "/images/Pokemon/Icons/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";

	$Icon_SS = SERVER_SPRITES . "/Pokemon/Icons/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
	if ( !file_exists($Icon_SS) )
		$Icon_SS = SERVER_SPRITES . "/Pokemon/Icons/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";

	echo "
		<table>
			<tbody>
				<tr>
					<td>
						<img src='$Sprite_DS' />
						<img src='$Icon_DS' />
					</td>
					<td>
						<img src='$Sprite_DR' />
						<img src='$Icon_DR' />
					</td>
					<td>
						<img src='$Sprite_SS' />
						<img src='$Icon_SS' />
					</td>
					<td>
						<img src='$Sprite_SR' />
						<img src='$Icon_SR' />
					</td>
				</tr>
			</tbody>
		</table>
	";
	
