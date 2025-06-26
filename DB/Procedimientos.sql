CREATE OR REPLACE PROCEDURE Procedure_LoginCliente(
    IN p_correo VARCHAR,
    IN p_contrasena VARCHAR,
    OUT resultado TEXT
)
LANGUAGE plpgsql
AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM Usuario u
        JOIN Clientes c ON u.IDUsuario = c.IDUsuario
        WHERE u.Correo = p_correo AND u.Contrasena = p_contrasena
    ) THEN
        resultado := 'LOGIN CLIENTE EXITOSO';
    ELSE
        resultado := 'CREDENCIALES INCORRECTAS CLIENTE';
    END IF;
END;
$$;

CREATE OR REPLACE PROCEDURE Procedure_LoginEmpresa(
    IN p_correo VARCHAR,
    IN p_contrasena VARCHAR,
    OUT resultado TEXT
)
LANGUAGE plpgsql
AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM Usuario u
        JOIN Empresa_Proveedora e ON u.IDUsuario = e.IDUsuario
        WHERE u.Correo = p_correo AND u.Contrasena = p_contrasena
    ) THEN
        resultado := 'LOGIN EMPRESA EXITOSO';
    ELSE
        resultado := 'CREDENCIALES INCORRECTAS EMPRESA';
    END IF;
END;
$$;