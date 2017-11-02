<table class="form-table">
  <thead>
    <tr valign="top">
      <th>Datum</th>
      <th>Wedstrijd</th>
      <th>Uitslag</th>
      <th>Wedstrijdnummer</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($data->results as $result) {
?>
<tr valign="top">
  <td>
    <?php echo date_i18n( 'd M', strtotime($result->wedstrijddatum)); ?>
  </td>
  <td><?php echo $result->wedstrijd; ?></td>
  <td><?php echo $result->uitslag; ?></td>
  <td><?php echo $result->wedstrijdnummer; ?></td>
</tr>
<?php
}
?>
  </tbody>
</table>