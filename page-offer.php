	<html>
<?php
	if (@$_POST['unlockoutputs'])
		if (no_displayed_error_result($result, multichain('lockunspent', true)))
			output_success_text('All outputs successfully unlocked');
	
	if (@$_POST['createoffer']) {
		if (no_displayed_error_result($prepare, multichain('preparelockunspentfrom',
				$_POST['from'], array($_POST['offerasset'] => floatval($_POST['offerqty']))))) {
			
			if ($_POST['askasset']== "INR"){
			if (no_displayed_error_result($rawexchange, multichain('createrawexchange',
				$prepare['txid'], $prepare['vout'], array($_POST['askasset'] => floatval($_POST['askqty'])))))
			
			
			
				output_success_text('Offer successfully prepared using transaction '.$prepare['txid'].' - please copy the raw offer below.');
				
				echo '<pre>'.html($rawexchange).'</pre>';
			}
			if ($_POST['askasset']== "Ether"){
			if (no_displayed_error_result($rawexchange, multichain('createrawexchange',
				$prepare['txid'], $prepare['vout'], array($_POST['askasset'] => floatval($_POST['evalue'])))))
			
			
			
				output_success_text('Offer successfully prepared using transaction '.$prepare['txid'].' - please copy the raw offer below.');
				
				echo '<pre>'.html($rawexchange).'</pre>';
			}
			if ($_POST['askasset']== "Bitcoin"){
			if (no_displayed_error_result($rawexchange, multichain('createrawexchange',
				$prepare['txid'], $prepare['vout'], array($_POST['askasset'] => floatval($_POST['bvalue'])))))
			
			
			
				output_success_text('Offer successfully prepared using transaction '.$prepare['txid'].' - please copy the raw offer below.');
				
				echo '<pre>'.html($rawexchange).'</pre>';
			}
			}
		}
	
?>

					<tr> 

								<form method="post">
								<th style="width:35%;">*1 Loyalty Point = </th>
								<td><input type="text" id = "rateinput" style="width:5%;" name = "rateinput" value="<?php echo isset($_POST['rateinput']) ? $_POST['rateinput'] : '' ?>" /></td>
								<th style="width:35%;">INR </th>								
								<th><input class=" col-sm-0.5 btn btn-default" type="button" OnClick="calculate();" name="Calculate" value="Calculate"></th>
		                         
							</form>
							
			</tr>
			<div class="row">
				<div class="col-sm-5">
					<h3>Available Balances</h3>
			
	<?php
	$sendaddresses=array();
	$usableaddresses=array();
	$keymyaddresses=array();
	$keyusableassets=array();
	$allassets=array();
	$haslocked=false;
	$getinfo=multichain_getinfo();
	$labels=array();
	

	
	$bitcoin = file_get_contents("https://blockchain.info/tobtc?currency=INR&value=1");
	$bit = (float)$bitcoin;
	$ether = file_get_contents("https://www.zebapi.com/api/v1/market/ticker-new/eth/inr");
	$var = json_decode($ether);
	$market = $var->market;
	$etherdiv= (1.0)/$market;
	
	
	if (no_displayed_error_result($getaddresses, multichain('getaddresses', true))) {
		if (no_displayed_error_result($listpermissions,
			multichain('listpermissions', 'send', implode(',', array_get_column($getaddresses, 'address')))
		))
			$sendaddresses=array_get_column($listpermissions, 'address');
			
		foreach ($getaddresses as $address)
			if ($address['ismine'])
				$keymyaddresses[$address['address']]=true;
				
		$labels=multichain_labels();

		if (no_displayed_error_result($listassets, multichain('listassets')))
			$allassets=array_get_column($listassets, 'name');
          $counter = 0;
		foreach ($sendaddresses as $address) {
		
			if (no_displayed_error_result($allbalances, multichain('getaddressbalances', $address, 0, true))) {
				
				if (count($allbalances)) {
					$assetunlocked=array();

					if (no_displayed_error_result($unlockedbalances, multichain('getaddressbalances', $address, 0, false))) {
						if (count($unlockedbalances))
							$usableaddresses[]=$address;
							
						foreach ($unlockedbalances as $balance)
							$assetunlocked[$balance['name']]=$balance['qty'];
					}
					
					$label=@$labels[$address];
?>
						<div><table id ="table<?php echo html($counter)?>" class="table table-bordered table-condensed table-break-words <?php echo ($address==@$getnewaddress) ? 'bg-success' : 'table-striped'?>">
<?php
			if (isset($label)) {
?>
							<tr>
								<th style="width:35%;">Label</th>
								<td><?php echo html($label)?></td>
							</tr>
<?php
			}
?>
							<tr>
								<th style="width:35%;">Address</th>
								<td class="td-break-words small"><?php echo html($address)?></td>
							</tr>
							
<?php
					foreach ($allbalances as $balance) {
					  if ($balance['name']== "Loyalty Points"){
					  	$converted = $balance['qty'];
						$bitcoinbalance = $bit*$converted;
						$etherbalance = $etherdiv*$converted;
						
					  }
						$unlockedqty=floatval($assetunlocked[$balance['name']]);
						$lockedqty=$balance['qty']-$unlockedqty;
						
						if ($lockedqty>0)
							$haslocked=true;
						if ($unlockedqty>0)
							$keyusableassets[$balance['name']]=true;
							
?>
							<tr>
								<th><?php echo html($balance['name'])?></th>
								<td><?php echo html($unlockedqty)?><?php echo ($lockedqty>0) ? (' ('.$lockedqty.' locked)') : ''?></td>
							</tr>
							
							
<?php
					}
?>	

<tr>
								<th style="width:35%;">Converted Loyalty Points in INR</th>
								<td class="td-break-words small"><?php echo html($converted)?></td>
								<tr>
								<th style="width:35%;">Converted Loyalty Points in Bitcoin</th>
								<td class="td-break-words small"><?php echo html($bitcoinbalance)?></td>
							</tr>
							<tr>
								<th style="width:35%;">Converted Loyalty Points in Ether</th>
								<td class="td-break-words small"><?php echo html($etherbalance)?></td>
							</tr>
							</tr>
						</table>
						</div>
<?php
				}
			}
			$counter = $counter+1;
		}
		
	}
	
	if ($haslocked) {
?>
				<form class="form-horizontal" method="post" action="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>">
					<input class="btn btn-default" type="submit" name="unlockoutputs" value="Unlock all outputs">
				</form>
<?php
	}
?>
				</div>
				
				<div class="col-sm-7">
					<h3>Create Offer</h3>
					
					<form class="form-horizontal" method="post" action="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>">
						<div class="form-group">
							<label for="from" class="col-sm-3 control-label">From address:</label>
							<div class="col-sm-9">
							<select class="form-control" name="from" id="from">
<?php
	foreach ($usableaddresses as $address) {
?>
								<option value="<?php echo html($address)?>"><?php echo format_address_html($address, true, $labels)?></option>
<?php
	}
?>						
							</select>
							</div>
						</div>
						<div class="form-group">
							<label for="offerasset" class="col-sm-3 control-label">Offer asset:</label>
							<div class="col-sm-9">
							<select class="form-control" name="offerasset" id="offerasset">
<?php
	foreach ($keyusableassets as $asset => $dummy) {
?>
								<option value="<?php echo html($asset)?>"><?php echo html($asset)?></option>
<?php
	}
?>						
							</select>
							</div>
						</div>
						<div class="form-group">
							<label for="offerqty" class="col-sm-3 control-label">Offer qty:</label>
							<div class="col-sm-9">
								<input class="form-control" name="offerqty" id="offerqty" placeholder="0.0">
							</div>
						</div>
						<div class="form-group">
							<label for="askasset" class="col-sm-3 control-label">Ask asset:</label>
							<div class="col-sm-9">
							<select class="form-control" name="askasset" id="askasset">
<?php
	foreach ($allassets as $asset) {
?>
								<option value="<?php echo html($asset)?>"><?php echo html($asset)?></option>
<?php
	}
?>						
							</select>
							</div>
						</div>
						<div class="form-group">
							<label for="askqty" class="col-sm-2 control-label">INR value:</label>
							<div class="col-sm-2">
								<input class="form-control"  name="askqty" id="askqty" >
							</div>
							
						

							<label for="bvalue" class="col-sm-2 control-label">Bitcoin value:</label>
							<div class="col-sm-2">
								<input class="form-control"  name="bvalue" id="bvalue" >
							</div> 
							
							

							<label for="evalue" class="col-sm-2 control-label">Ether value:</label>
							<div class="col-sm-2">
								<input class="form-control"  name="evalue" id="evalue" >
							</div>
							</div>

						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-9">	
								<input class="btn btn-default" type="submit" name="createoffer" value="Create Offer">
							</div>
						</div>
					</form>

				</div>
			</div>
			<script>	
  
  
      
var bitcoinvalue = <?php echo $bit; ?>;		
var ethervalue = <?php echo $etherdiv; ?>;
var counterval = <?php echo $counter; ?>;

	function calculate()
	 
{ 
	var Offerquantity = parseInt(document.getElementById("offerqty").value);	
	var inputrate = parseInt(document.getElementById("rateinput").value);
	 
	for (var i =0; i <counterval; i++){	
	var k=0;
	for(var j =0; j <=4; j ++){
	if (document.getElementById("table"+i).rows[j+2].cells[0].innerHTML== "Loyalty Points")
	{
	var loyalty = document.getElementById("table"+i).rows[j+2].cells[1].innerHTML;
	}
	if (document.getElementById("table"+i).rows[2+k].cells[0].innerHTML== "Converted Loyalty Points in INR"){
	document.getElementById("table"+i).rows[2+k].cells[1].innerHTML=loyalty*inputrate;
	document.getElementById("table"+i).rows[3+k].cells[1].innerHTML=loyalty*inputrate*bitcoinvalue;
	document.getElementById("table"+i).rows[4+k].cells[1].innerHTML= loyalty*inputrate*ethervalue;
	}
	else {
		k++;
	}
	}		
	
	var inr= Offerquantity*inputrate;
	var btc= bitcoinvalue*inr;
	var eth= ethervalue*inr;
	 
	document.getElementById("askqty").value= inr;
	document.getElementById("bvalue").value= btc;
	document.getElementById("evalue").value= eth;
	if (document.getElementById("offerasset").value ==document.getElementById("askasset").value ){
		alert("Choose different assets!");
	}
	}
	}

	
	

</script>



			
</html>