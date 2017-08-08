<table class="form-table">
  <thead>
    <tr valign="top">
      <th>Team</th>
      <th>Teamcode</th>
      <th>Poulecode</th>
      <th>Leeftijdscategorie</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($data->teams as $team) {
?>
<tr valign="top">
  <td><?php echo $data->clubInfo->clubnaam . " " . $team->teamnaam; ?></td>
  <td><?php echo $team->teamcode; ?></td>
  <td><?php echo $team->poulecode; ?></td>
  <td><?php echo $team->leeftijdscategorie; ?></td>
</tr>
<?php
}
?>
  </tbody>
</table>
