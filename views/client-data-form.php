<section class="tda-client-form" data-tda-client-form>
	<header class="tda-client-form__header">
		<p>Completa los datos de tu vehículo y adjunta la documentacion necesaria.</p>
	</header>

	<form class="tda-client-form__form" method="post" enctype="multipart/form-data" novalidate>
		<fieldset class="tda-client-form__group">
			<legend>Datos del vehiculo</legend>

			<div class="tda-client-form__group-label tda-client-form__group-label__full-width">
				<label for="vehicle_plate">Matricula</label>
				<input id="vehicle_plate" name="vehicle_plate" type="text" placeholder="Ej. 1234ABC">
			</div>

			<div class="tda-client-form__group-label tda-client-form__group-label__full-width">
				<label for="vehicle_notes">Observaciones</label>
				<textarea id="vehicle_notes" name="vehicle_notes" rows="4"
					placeholder="Escribe aqui cualquier detalle relevante"></textarea>
			</div>
		</fieldset>

		<fieldset class="tda-client-form__group">
			<legend>Adjuntos</legend>

			<div class="tda-client-form__group-label">
				<label for="vehicle_technical_sheet">Ficha tecnica del vehiculo</label>
				<input id="vehicle_technical_sheet" name="vehicle_technical_sheet" type="file" required
					accept=".pdf,.jpg,.jpeg,.png,.webp">
			</div>

			<div class="tda-client-form__group-label">
				<label for="vehicle_registration_permit">Permiso de circulacion</label>
				<input id="vehicle_registration_permit" name="vehicle_registration_permit" type="file" required
					accept=".pdf,.jpg,.jpeg,.png,.webp">
			</div>
		</fieldset>

		<label class="tda-client-form__checkbox" for="privacy_accept">
			<input id="privacy_accept" name="privacy_accept" type="checkbox" value="1" required>
			<span>Acepto la politica de privacidad y el tratamiento de mis datos.</span>
		</label>

		<div class="tda-client-form__actions">
			<button type="submit">Enviar formulario</button>
		</div>
	</form>
</section>