# Setting Up Git Remote with SSH for This Project

This guide documents the exact steps taken to securely configure the remote repository for this codebase using SSH authentication.

## 1. Generate a Dedicated SSH Key

Run the following command to generate a new SSH key for this project:

```bash
ssh-keygen -t rsa -b 4096 -f ~/.ssh/metasoft_letterhead -C "info@metasoftdevs.com"
```

-   This creates a private key (`~/.ssh/metasoft_letterhead`) and a public key (`~/.ssh/metasoft_letterhead.pub`).

## 2. Add the Public Key to GitHub

-   Open the public key file:
    ```bash
    cat ~/.ssh/metasoft_letterhead.pub
    ```
-   Copy the entire contents.
-   Go to [GitHub SSH keys settings](https://github.com/settings/keys) for the Metasoftdevs account.
-   Click "New SSH key", give it a descriptive title, and paste the key.
-   Save.

## 3. Configure SSH for This Repository

-   Set the remote URL to use SSH:
    ```bash
    git remote set-url origin git@github.com:Metasoftdevs/letterhead-generator.git
    ```

## 4. Test SSH Connection

-   Run:
    ```bash
    ssh -T git@github.com
    ```
-   You should see:
    `Hi Metasoftdevs! You've successfully authenticated, but GitHub does not provide shell access.`

## 5. Use SSH for Git Operations

-   You can now use `git pull`, `git push`, etc., and your repository will use the dedicated SSH key for authentication.

---

**Note:**

-   If you want to use a custom SSH config for this key, edit `~/.ssh/config` and add:
    ```
    Host github.com-metasoft-letterhead
        HostName github.com
        User git
        IdentityFile ~/.ssh/metasoft_letterhead
    ```
-   Then set the remote URL to:
    ```bash
    git remote set-url origin github.com-metasoft-letterhead:Metasoftdevs/letterhead-generator.git
    ```

This ensures your project uses a dedicated SSH key for secure GitHub access.
