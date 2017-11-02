<table class="form-table teams">
  <thead>
    <tr valign="top">
      <th>Teamcode</th>
      <th>Team</th>
      <th>Competities</th>
      <th>Leeftijdscategorie</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($data->teams as $team) {
?>
<tr>
  <td class="teams__code" width="80"><?php echo $team->teamcode; ?></td>
  <td><strong><?php echo $data->clubInfo->clubnaam . " " . $team->teamnaam; ?></strong></td>
  <td>
    <table class="form-table competitions">
    <?php
    foreach ($team->competitions as $competition) {
      ?>
      <tr>
        <td class="competitions__code"><?php echo $competition->poulecode; ?></td>
        <td><?php echo $competition->teamnaam; ?></td>
      </tr>
      <?php
    }
    ?>
    </table>
  </td>
  <td><?php echo $team->leeftijdscategorie; ?></td>
</tr>
<?php
}
?>
  </tbody>
</table>
<style>
  table.teams td {
    vertical-align: top;
  }

  table.competitions {
    margin-left: -20px;
    margin-top: -15px;
  }

  .competitions__code,
  .teams__code {
    width: 80px;
  }
</style>