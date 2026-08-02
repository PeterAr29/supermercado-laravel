<div class="row">
    <div class="col-md-6 mb-3">
        <label>Nombre</label>
        <input type="text" name="nombre" class="form-control"
               value="{{ old('nombre', $proveedor->nombre ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>RUC</label>
        <input type="text" name="ruc" class="form-control"
               value="{{ old('ruc', $proveedor->ruc ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Teléfono</label>
        <input type="text" name="telefono" class="form-control"
               value="{{ old('telefono', $proveedor->telefono ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email', $proveedor->email ?? '') }}">
    </div>

    <div class="col-md-12 mb-3">
        <label>Dirección</label>
        <input type="text" name="direccion" class="form-control"
               value="{{ old('direccion', $proveedor->direccion ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Nombre del contacto</label>
        <input type="text" name="contacto_nombre" class="form-control"
               value="{{ old('contacto_nombre', $proveedor->contacto_nombre ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label>Teléfono del contacto</label>
        <input type="text" name="contacto_telefono" class="form-control"
               value="{{ old('contacto_telefono', $proveedor->contacto_telefono ?? '') }}">
    </div>
</div>
