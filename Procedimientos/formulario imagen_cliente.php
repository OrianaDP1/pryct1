<form action="guardar imagen_cliente.php" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="idusuario" value="2" />
  <input type="file" name="imagen" accept="image/*" required />
  <button type="submit">Subir Imagen</button>
</form>