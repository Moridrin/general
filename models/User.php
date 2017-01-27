<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 14:48
 */
class User extends \WP_User
{
    #region Get User
    #region construct
    /**
     * User constructor.
     *
     * @param \WP_User $user the WP_User component used as base for the User
     */
    function __construct($user)
    {
        parent::__construct($user);
    }
    #endregion

    #region By ID
    /**
     * This function searches for a User by its ID.

     *
*@param int $id is the ID used to find the SSV_User
     *
     * @return User|null returns the User it found or null if it can't find one.
     */
    public static function getByID($id)
    {
        if ($id == null) {
            return null;
        }
        return new User(get_user_by('id', $id));
    }
    #endregion

    #region Current
    public static function getCurrent()
    {
        if (!is_user_logged_in()) {
            return null;
        }
        return new User(wp_get_current_user());
    }
    #endregion
    #endregion

    #region register($user, $password, $email)
    /**
     * @param $username
     * @param $password
     * @param $email
     *
     * @return Message|User
     */
    public static function register($username, $password, $email)
    {
        if (empty($username) || empty($password) || empty($email)) {
            return new Message('You cannot register without Username, Password and Email.', Message::ERROR_MESSAGE);
        }
        if (username_exists($username)) {
            return new Message('This username already exists.', Message::ERROR_MESSAGE);
        }
        if (email_exists($email)) {
            return new Message('This email address already exists. Try resetting your password.', Message::ERROR_MESSAGE);
        }
        $id = wp_create_user(
            SSV_General::sanitize($username),
            SSV_General::sanitize($password),
            SSV_General::sanitize($email)
        );

        return self::getByID($id);
    }

    #endregion

    #region getDefaultFields()
    public static function getDefaultFields()
    {
        #region Username
        /** @var TextInputField $usernameField */
        $usernameField = Field::fromJSON(
            json_encode(
                array(
                    'id'            => -1,
                    'title'         => 'Username',
                    'field_type'    => 'input',
                    'input_type'    => 'text',
                    'name'          => 'username',
                    'disabled'      => false,
                    'required'      => true,
                    'default_value' => '',
                    'placeholder'   => '',
                    'class'         => '',
                    'style'         => '',
                )
            )
        );
        #endregion

        #region Email
        /** @var TextInputField $emailField */
        $emailField = Field::fromJSON(
            json_encode(
                array(
                    'id'            => -1,
                    'title'         => 'Email',
                    'field_type'    => 'input',
                    'input_type'    => 'text',
                    'name'          => 'email',
                    'disabled'      => false,
                    'required'      => true,
                    'default_value' => '',
                    'placeholder'   => '',
                    'class'         => '',
                    'style'         => '',
                )
            )
        );
        #endregion

        #region Password
        /** @var CustomInputField $passwordField */
        $passwordField = Field::fromJSON(
            json_encode(
                array(
                    'id'            => -1,
                    'title'         => 'Password',
                    'field_type'    => 'input',
                    'input_type'    => 'password',
                    'name'          => 'password',
                    'disabled'      => false,
                    'required'      => true,
                    'default_value' => '',
                    'placeholder'   => '',
                    'class'         => 'validate',
                    'style'         => '',
                )
            )
        );
        #endregion

        #region Confirm Password
        /** @var CustomInputField $confirmPasswordField */
        $confirmPasswordField = Field::fromJSON(
            json_encode(
                array(
                    'id'            => -1,
                    'title'         => 'Confirm Password',
                    'field_type'    => 'input',
                    'input_type'    => 'password',
                    'name'          => 'password_confirm',
                    'disabled'      => false,
                    'required'      => true,
                    'default_value' => '',
                    'placeholder'   => '',
                    'class'         => 'validate',
                    'style'         => '',
                )
            )
        );
        #endregion

        return array(
            $usernameField->name        => $usernameField,
            $emailField->name           => $emailField,
            $passwordField->name        => $passwordField,
            $confirmPasswordField->name => $confirmPasswordField,
        );
    }
    #endregion

    #region isCurrentUser()
    /**
     * @return bool returns true if this is the current user.
     */
    public function isCurrentUser()
    {
        if ($this->ID == wp_get_current_user()->ID) {
            return true;
        } else {
            return false;
        }
    }
    #endregion

    #region isBoard()
    /**
     * @return bool true if this user has the board role (and can edit other member profiles).
     */
    public function isBoard()
    {
        return in_array(get_option(SSV_General::OPTION_BOARD_ROLE), $this->roles);
    }
    #endregion

    #region checkPassword($password)
    /**
     * @param string $password The plaintext new user password
     *
     * @return bool false, if the $password does not match the member's password
     */
    public function checkPassword($password)
    {
        return wp_check_password($password, $this->data->user_pass, $this->ID);
    }
    #endregion

    #region update($inputFields)
    /**
     * This method updates all
     *
     * @param InputField[] $inputFields
     */
    public function update($inputFields)
    {
        foreach ($inputFields as $field) {
            $this->updateMeta($field->name, $field->value);
        }
    }
    #endregion

    #region updateMeta($meta_key, $value)
    /**
     * This function sets the metadata defined by the key (or an alias of that key).
     * The aliases are:
     *  - email, email_address, member_email => user_email
     *  - name => display_name
     *  - login, username, user_name => user_login
     * The function will also update the display name after the first or last name is updated.
     *
     * @param string $meta_key the key that defines which metadata to set.
     * @param string $value    the value to set.
     *
     * @return bool|Message true if success, else it provides an object consisting of a message and a type (notification or error).
     */
    function updateMeta($meta_key, $value)
    {
        $value = SSV_General::sanitize($value);
        if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
            wp_update_user(array('ID' => $this->ID, 'user_email' => $value));
            update_user_meta($this->ID, 'user_email', $value);
            $this->user_email = $value;
            return true;
        } elseif ($meta_key == "name" || $meta_key == "display_name") {
            wp_update_user(array('ID' => $this->ID, 'display_name' => $value));
            update_user_meta($this->ID, 'display_name', $value);
            $this->display_name = $value;
            return true;
        } elseif ($meta_key == "first_name" || $meta_key == "last_name") {
            update_user_meta($this->ID, $meta_key, $value);
            $display_name = $this->getMeta('first_name') . ' ' . $this->getMeta('last_name');
            wp_update_user(array('ID' => $this->ID, 'display_name' => $display_name));
            update_user_meta($this->ID, 'display_name', $display_name);
            $this->display_name = $display_name;
            return true;
        } elseif ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
            return new Message('Cannot change the user-login. Please consider setting the field display to \'disabled\'', Message::NOTIFICATION_MESSAGE); //cannot change user_login
        } else {
            update_user_meta($this->ID, $meta_key, $value);
            return true;
        }
    }
    #endregion

    #region getMeta($meta_key, $default)
    /**
     * This function returns the metadata associated with the given key (or an alias of that key).
     * The aliases are:
     *  - email, email_address, member_email => user_email
     *  - name => display_name
     *  - login, username, user_name => user_login
     *
     * @param string $meta_key defines which metadata should be returned.
     * @param mixed  $default  is the value returned if there is no value associated with the key.
     *
     * @return string the value associated with the key or the default value if there is no value associated with the key.
     */
    function getMeta($meta_key, $default = '')
    {
        if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
            return $this->user_email;
        } elseif ($meta_key == "name" || $meta_key == "display_name") {
            return $this->display_name;
        } elseif ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
            return $this->user_login;
        } else {
            return stripslashes(get_user_meta($this->ID, $meta_key, true));
        }
    }
    #endregion

    #region getProfileLink($target)
    /**
     * @param string $target
     *
     * @return string of the full <a> tag.
     */
    public function getProfileLink($target = '')
    {
        $href   = esc_url($this->getProfileURL());
        $target = empty($target) ? '' : 'target="' . $target . '"';
        $label  = $this->display_name;
        return "<a href='$href' $target>$label</a>";
    }

    #endregion

    #region getProfileURL()
    /**
     * @return string the url for the users profile
     */
    public function getProfileURL()
    {
        $url = get_edit_user_link();
        $url = apply_filters(SSV_General::HOOK_USER_PROFILE_URL, $url);
        return $url;
    }
    #endregion
}
