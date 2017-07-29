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
$this->addAgeCategoryToTeams($this->teams);
$this->teams = $this->orderTeamsByCategory($this->teams);

foreach ($this->teams as $team) {
?>
<tr valign="top">
  <td><?php echo $this->clubInfo->clubnaam . " " . $team->teamnaam; ?></td>
  <td><?php echo $team->teamcode; ?></td>
  <td><?php echo $team->poulecode; ?></td>
  <td><?php echo $team->leeftijdscategorie; ?></td>
  <td><?php echo $team->leeftijdscategorieid; ?></td>
</tr>
<?php
}
?>
  </tbody>
</table>
