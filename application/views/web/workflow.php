<!DOCTYPE html>
<html>
<head>
	<title>Workflow Hunt</title>

	<!-- Custom Font -->
	<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet"> 

	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/css/bootstrap.min.css" integrity="sha384-2hfp1SzUoho7/TsGGGDaFdsuuDL0LX2hnUp6VkX3CUQ2K4K+xjboZdsXyp4oUHZj" crossorigin="anonymous">

	<!-- Custom CSS-->
	<link rel="stylesheet" href="<?php print base_url();?>assets/css/main.css">
</head>
<body>
	<div class="container results-container">
		<div class="row">
			<div class="col-lg-2 col-xl-2 text-xs-center text-lg-right">
				<a href="<?php print base_url();?>index.php/web/index">
					<img src="<?php print base_url();?>assets/img/logo.png" class="results-logo">
				</a>
			</div>
			<!-- .col-lg-2 .col-xl-2 .text-xs-center .text-lg-right -->
			<div class="col-lg-10 col-xl-10">
				<form action="<?php print base_url();?>index.php/web/results/" method="GET">
					<div class="input-group">
						<input type="text" class="form-control results-input-search" name="query">
						<span class="input-group-btn">
					        <button class="btn btn-primary results-btn-search" type="submit">
					        	<span class="fa fa-search"> </span>
					        </button>
					        <!-- .results-btn-search -->
					    </span>
					    <!-- .input-group-btn -->
					</div>
					<!-- .input-group -->
				</form>
				<!-- form -->
			</div>
			<!-- .col-lg-10 .col-xl-10 -->
		</div>
		<!-- .row -->
		<hr>
		<div class="row">
			<div class="col-lg-10 col-xl-10 offset-lg-2 offset-xl-2">
				<h2>Semantic Annotation</h2>
				</br>
				<div>
					<strong>Title: </strong>
					<span id="workflow-title">
						<?php print $title; ?>
					</span>
				</div>
				<div class="margin-top-05">
					<strong>URL: </strong>
					<a href="http://www.myexperiment.org/workflows/<?php print $id;?>" target="_blank" rel="noopener noreferrer">
						http://www.myexperiment.org/workflows/<?php print $id;?>
					</a>				
				</div>
				<div class="margin-top-05">
					<strong>Description: </strong>
					<span id="workflow-description">
						<?php print $description; ?>
					</span>
				</div>
				<div class="margin-top-05">
					<strong>Tags: </strong>
					<span id="workflow-tags">
						<?php print $tags; ?>
					</span>
				</div>
				<div class="margin-top-05">
					<strong>Ontologies:</strong>
				<?php 
					foreach ($ontologies as $ontology) {
				?>
					<div>
						<span style="background-color:<?php print $ontology->color; ?>;" class="ontology-square-color" ></span>
						<span class="ontology-text"><?php print $ontology->prefix; ?></span>
					</div>
				<?php
					}
				?>
				</div>
				</br>
			</div>
			<!-- .col-lg-10 .col-xl-10 .offset-lg-2 .offset-xl-2-->
		</div>
	</div>
</body>
</html>
