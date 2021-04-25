# Start the SSH server
if [ "${ENABLE_BUILTIN_SSH}" = "1" ]
then
    sudo /etc/init.d/ssh restart
fi

echo "Setting permissions for the docker container..."
sudo -E /fixperms.sh || true
sudo chown -R ${LOCAL_UID}:${LOCAL_UID} /var/www/symfony || true
echo "Done."

echo "Fixing /dev/shm permissions"
sudo chown ${LOCAL_UID}:${LOCAL_GID} -R /dev/shm

# Execute the CMD
exec "$@"