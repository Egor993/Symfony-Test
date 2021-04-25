#!/bin/bash
set -e

# @see
# copy-pasted from https://github.com/schmidigital/permission-fix

UNUSED_USER_ID=21338
UNUSED_GROUP_ID=21337

echo "Fixing permissions."

# Setting Group Permissions
DOCKER_GROUP_CURRENT_ID=`id -g $APP_GROUP`

if [ $DOCKER_GROUP_CURRENT_ID -eq $LOCAL_GID ]; then
  echo "Group $APP_GROUP is already mapped to $DOCKER_GROUP_CURRENT_ID. Nice!"
else
  echo "Check if group with ID $LOCAL_GID already exists"
  DOCKER_GROUP_OLD=`getent group $LOCAL_GID | cut -d: -f1`

  if [ -z "$DOCKER_GROUP_OLD" ]; then
    echo "Group ID is free. Good."
  else
    echo "Group ID is already taken by group: $DOCKER_GROUP_OLD"

    echo "Changing the ID of $DOCKER_GROUP_OLD group to 21337"
    groupmod -o -g $UNUSED_GROUP_ID $DOCKER_GROUP_OLD
  fi

  echo "Changing the ID of $APP_GROUP group to $LOCAL_GID"
  groupmod -o -g $LOCAL_GID $APP_GROUP || true
  echo "Finished"
  echo "-- -- -- -- --"
fi

# Setting User Permissions
DOCKER_USER_CURRENT_ID=`id -u $APP_USER`

if [ $DOCKER_USER_CURRENT_ID -eq $LOCAL_UID ]; then
  echo "User $APP_USER is already mapped to $DOCKER_USER_CURRENT_ID. Nice!"

else
  echo "Check if user with ID $LOCAL_UID already exists"
  DOCKER_USER_OLD=`getent passwd $LOCAL_UID | cut -d: -f1`

  if [ -z "$DOCKER_USER_OLD" ]; then
    echo "User ID is free. Good."
  else
    echo "User ID is already taken by user: $DOCKER_USER_OLD"

    echo "Changing the ID of $DOCKER_USER_OLD to 21337"
    usermod -o -u $UNUSED_USER_ID $DOCKER_USER_OLD
  fi

  echo "Changing the ID of $APP_USER user to $LOCAL_UID"
  usermod -o -u $LOCAL_UID $APP_USER || true
  echo "Finished"
fi

