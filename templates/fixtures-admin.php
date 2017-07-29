<table class="form-table">
  <thead>
    <tr valign="top">
      <th>Datum</th>
      <th>Tijd</th>
      <th>Wedstrijd</th>
      <th>ID</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($fixtures as $fixture) {
  $date = date_create($fixture->wedstrijddatum);
?>
<tr valign="top">
  <td>
    <?php echo $date->format("l d F"); ?>
  </td>
  <td>
    <?php echo $date->format("H:i"); ?>
  </td>
  <td><?php echo $fixture->wedstrijd; ?></td>
  <td><?php echo $fixture->leeftijdscategorieid; ?></td>
</tr>
<?php
}
?>
  </tbody>
</table>
