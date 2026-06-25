<section class="tda-client-form" data-tda-client-form>
	<header class="tda-client-form__header">
		<h2>Formulario de Datos del Cliente</h2>
		<p>Completa tus datos y adjunta la documentacion necesaria.</p>
	</header>

	<form class="tda-client-form__form" method="post" enctype="multipart/form-data" novalidate>
		<fieldset class="tda-client-form__group">
			<legend>Datos del cliente</legend>

			<div class="tda-client-form__group-label">
				<label for="client_name">Nombre y apellidos</label>
				<input id="client_name" name="client_name" type="text" autocomplete="name" required>
			</div>

			<div class="tda-client-form__group-label">
				<label for="client_email">Correo electronico</label>
				<input id="client_email" name="client_email" type="email" autocomplete="email" required>
			</div>

			<div class="tda-client-form__group-label">
				<label for="client_phone">Telefono</label>
				<input id="client_phone" name="client_phone" type="tel" autocomplete="tel" required>
			</div>

			<div class="tda-client-form__group-label">
				<label for="client_nif">NIF / NIE</label>
				<input id="client_nif" name="client_nif" type="text" required>
			</div>
		</fieldset>

		<fieldset class="tda-client-form__group">
			<legend>Datos del vehiculo</legend>

			<div class="tda-client-form__group-label">
				<label for="vehicle_vin">VIN</label>
				<input id="vehicle_vin" name="vehicle_vin" type="text" placeholder="Ej. VF1ABC12345678901">
			</div>

			<div class="tda-client-form__group-label">
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

			<div class="tda-client-form__group-label tda-client-form__group-label__full-width">
				<label for="client_files">Subir archivos</label>
				<input id="client_files" name="client_files[]" type="file" multiple
					accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
				<small>Puedes seleccionar varios archivos a la vez (DNI, permiso, ficha tecnica, etc.).</small>
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