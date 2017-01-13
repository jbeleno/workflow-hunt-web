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
						<input type="text" class="form-control results-input-search" name="query" value="<?php print $query; ?>">
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
				<?php 	
					if($status == 'BAD')
					{
				?>
					<div class="results-not-found">
						<p>
							Your search - <strong><?php print $query; ?></strong> - 
							did not match any workflow.
						</p>
						<p>Suggestions:</p>
						<ul>
							<li>Ensure all words are spelled correctly.</li>
							<li>Try using different words or synonyms.</li>
							<li>Try using more general keywords.</li>
							<li>Make your queries as concise as possible.</li>
						</ul>
					</div>
					<!-- .results-not-found -->
				<?php 	
					}
					else
					{
				?>
					<div class="results-found">
						<p><?php print $total; ?> Resultados</p>
						<div class="results-content">
							<?php
								foreach ($results as $workflow) 
								{
							?>
								<div class="results-workflow">
									<a href="http://www.myexperiment.org/workflows/<?php print $workflow['_id'];?>" class="results-workflow-title" target="_blank" rel="noopener noreferrer">
										<?php print $workflow['_source']['title'];?>
									</a>
									<div class="results-workflow-url">
										http://www.myexperiment.org/workflows/<?php print $workflow['_id'];?>
									</div>
									<div class="results-workflow-description">

										<?php print character_limiter($workflow['_source']['description'], 320);?>
									</div>
									<div class="results-workflow-wfms">
										Workflow Management System: <strong>Taverna</strong>
									</div>
								</div>
								<!-- .results-workflow -->
							<?php
								}
							?>
						</div>
						<!-- .results-content -->
						<nav aria-label="..." class="text-xs-center">
							<ul class="pagination pagination-sm">
								<?php print $pagination->create_links(); ?>
							</ul>
							<!-- ul -->
						</nav>
						<!-- nav -->
					</div>
					<!-- .results-found -->
				<?php 	
					}
				?>
			</div>
			<!-- .col-lg-10 .col-xl-10 .offset-lg-2 .offset-xl-2-->
		</div>
	</div>
</body>
</html>