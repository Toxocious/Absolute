<?php
	require_once '../core/required/session.php';
	require_once '../core/functions/staff.php';

	try
	{
		$Query_Movedex = $PDO->prepare("SELECT * FROM `moves` ORDER BY `id` ASC");
		$Query_Movedex->execute();
		$Query_Movedex->setFetchMode(PDO::FETCH_ASSOC);
		$Movedex = $Query_Movedex->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='head'>Edit Moves</div>
<div class='body'>
	<div class='description' style='margin-bottom: 5px;'>
		Click on the move that you would like to edit.
	</div>

	<div class='row'>
		<div class='panel' style='float: left; margin-right: 5px; width: calc(100% / 2 - 2.5px);'>
			<div class='head'>Move List</div>
			<div class='body' style='height: 563px; overflow: auto; padding-top: 3px;'>
				<table>
					<tr>
						<td>
							<?php
								foreach ( $Movedex as $Key => $Value )
								{
									echo "
										<b>[</b> <a href='javascript:void(0);' onclick='LoadContent(\"ajax/edit_moves.php\", \"AJAX\", { Fetch: {$Value['id']} });'>{$Value['name']}</a> <b>]</b>
									";

									if ( $Key % 4 == 3 )
									{
										echo "<br />";
									}
								}
							?>
						</td>
					</td>
				</table>
			</div>
		</div>

		<div class='panel' style='float: left; width: calc(100% / 2 - 2.5px);'>
			<div class='head'>Selected Move</div>
			<div class='body' id='AJAX' style='padding: 5px;'>
					Please select a move.
			</div>
		</div>
	</div>
</div>