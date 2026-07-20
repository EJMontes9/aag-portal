# Manual — Usuarios y roles

Cómo dar acceso al panel y decidir qué puede hacer cada persona.

---

## 1. Los cinco roles

| Rol | Para quién | Qué puede hacer |
|---|---|---|
| **super_admin** | Responsable técnico | Todo, incluida la gestión de usuarios y roles |
| **admin** | Responsable del portal | Todo el contenido y la configuración. **No** gestiona usuarios ni roles |
| **publisher** | Comunicación | Crea, edita y **publica** contenido. Sin datos personales ni configuración |
| **editor** | Redacción | Redacta y edita. **No elimina** ni ve datos personales |
| **transparencia** | Encargado de LOTAIP | **Solo** Transparencia: años, meses, documentos y galería de medios |

Un usuario puede tener **varios roles**; se suman los permisos.

### Qué ve cada uno

| | super_admin | admin | publisher | editor | transparencia |
|---|:---:|:---:|:---:|:---:|:---:|
| Noticias, páginas, proyectos | ✅ | ✅ | ✅ | ✅ | — |
| Eliminar contenido | ✅ | ✅ | ✅ | — | — |
| Transparencia (LOTAIP) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Galería de medios | ✅ | ✅ | ✅ | ✅ | ✅ |
| Suscriptores y formularios recibidos | ✅ | ✅ | — | — | — |
| Ajustes del sitio | ✅ | ✅ | — | — | — |
| Registro de actividad | ✅ | ✅ | ✅ | — | — |
| **Usuarios y roles** | ✅ | — | — | — | — |

> Suscriptores y envíos de formulario contienen **datos personales** (correo,
> IP, navegador). Por eso solo los ven los administradores.

---

## 2. Crear un usuario

**Panel → Usuarios y Roles → Usuarios → Nuevo usuario**

1. Nombre y correo (con ese correo iniciará sesión).
2. Contraseña: mínimo 12 caracteres. **Comunícasela por un medio seguro**, no
   por correo junto con el usuario.
3. Marca uno o más roles. Cada uno lleva su descripción en el formulario.

La persona ya puede entrar en `/admin`. Pídele que **cambie la contraseña** en
su primer acceso, desde el menú de usuario → Perfil.

### Cambiar la contraseña de alguien

Edita su usuario y escribe una nueva. Si dejas el campo **vacío**, la
contraseña no cambia.

---

## 3. Crear un rol a medida

**Panel → Usuarios y Roles → Roles → Nuevo**

Verás la lista completa de permisos agrupados por recurso. Cada recurso tiene
sus acciones: ver, crear, editar, eliminar…

Ejemplo — un rol que solo publique noticias:

1. Nombre: `noticias`
2. Marca en **News**: `view_any`, `view`, `create`, `update`
3. Marca en **Media**: `view_any`, `create` (para subir imágenes)
4. No marques nada más

> Tras crear un rol nuevo, para que sus usuarios puedan **entrar** al panel
> hay que añadirlo a la lista de `canAccessPanel()` en `app/Models/User.php`.
> Es una línea; sin ella el rol existe pero no da acceso.

---

## 4. Reglas de seguridad

Están puestas a propósito y no se pueden saltar desde la interfaz:

**Solo un `super_admin` puede otorgar el rol `super_admin`.**
Si no, cualquier administrador podría auto-ascenderse y la separación de roles
no serviría de nada.

**Solo `super_admin` gestiona usuarios y roles.**
Quien crea cuentas y asigna permisos puede, en la práctica, concederse
cualquier permiso.

**Nadie puede borrar su propia cuenta.**
Ni siquiera un `super_admin`. Es la forma más común de quedarse fuera del
panel sin poder volver a entrar.

**Nadie puede quitarse a sí mismo el acceso.**
Al editarse, si se desmarcan todos los roles con acceso al panel, se conservan
los anteriores y aparece un aviso.

---

## 5. Si te quedas fuera del panel

Necesitas acceso al servidor por SSH:

```bash
cd /home/USUARIO/laravel_app

# Ver los usuarios
php artisan tinker --execute="App\Models\User::all(['id','name','email'])->each(fn(\$u) => print(\$u->id.' '.\$u->email.PHP_EOL));"

# Cambiar una contraseña
php artisan tinker --execute="\$u = App\Models\User::find(1); \$u->password = Hash::make('NuevaClaveSegura123'); \$u->save();"

# Devolver el rol de super_admin
php artisan shield:super-admin --user=1
```

---

## 6. Buenas prácticas

- **Una cuenta por persona.** Nada de cuentas compartidas: el registro de
  actividad deja de servir para nada si no se sabe quién hizo qué.
- **El rol más bajo que sirva.** Si alguien solo redacta, `editor` basta.
- **Revisa las cuentas al menos una vez al año** y da de baja a quien ya no
  trabaje en el portal.
- **Un solo `super_admin`** (dos como mucho, por si acaso).
- Comprueba en **Registro de actividad** quién ha hecho cambios importantes.
