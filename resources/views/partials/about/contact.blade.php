
		<!-- SECTION -->
		<div class="section py-4">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-sm-9 px-4 mx-auto">
                        <h1 class="h1 mb-5 fw-bold">{{ $entity_title }}</h1>
                        <p class="mb-5 fs-6" style="color: #444; text-align: justify;">{!! $entity_description !!}</p>

                        <div class="card card-body">
                            <form id="contactForm" data-sb-form-api-token="API_TOKEN">
                                <!-- Name input-->
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="name" type="text" placeholder="Enter your name..." data-sb-validations="required" />
                                    <label for="name">Nom complet</label>
                                    <div class="invalid-feedback" data-sb-feedback="name:required">Le nom est obligatoire.</div>
                                </div>

                                <!-- Email address input-->
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="email" type="email" placeholder="name@example.com" data-sb-validations="required,email" />
                                    <label for="email">Adresse e-mail</label>
                                    <div class="invalid-feedback" data-sb-feedback="email:required">Le mail est obligatoire.</div>
                                    <div class="invalid-feedback" data-sb-feedback="email:email">Ce mail n'est pas valide.</div>
                                </div>

                                <!-- Phone number input-->
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="phone" type="tel" placeholder="(123) 456-7890" data-sb-validations="required" />
                                    <label for="phone">N° de téléphone</label>
                                    <div class="invalid-feedback" data-sb-feedback="phone:required">Le n° de téléphone est obligatoire.</div>
                                </div>

                                <!-- Message input-->
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="message" type="text" placeholder="Enter your message here..." style="height: 10rem" data-sb-validations="required"></textarea>
                                    <label for="message">Message</label>
                                    <div class="invalid-feedback" data-sb-feedback="message:required">Le message est obligatoire.
                                    </div>
                                </div>

                                <!-- Submit success message-->
                                <!---->
                                <!-- This is what your users will see when the form-->
                                <!-- has successfully submitted-->
                                <div class="d-none" id="submitSuccessMessage">
                                    <div class="text-center mb-3">
                                        <div class="fw-bolder">Message envoyé!</div>
                                    </div>
                                </div>

                                <!-- Submit error message-->
                                <!---->
                                <!-- This is what your users will see when there is-->
                                <!-- an error submitting the form-->
                                <div class="d-none" id="submitErrorMessage">
                                    <div class="text-center text-danger mb-3">Erreur d'envoi de message!</div>
                                </div>

                                <!-- Submit Button-->
                                <div class="d-grid">
                                    <button class="btn btn-warning rounded-pill btn-lg" id="submitButton" type="submit">Envoyer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->
