#!/bin/bash

# A script to automate the release process.
#
# USAGE:
# ./release.sh <feature-branch> <version>
# Example: ./release.sh feature/add-tags v1.1.0

# --- Configuration ---
# The name of your primary development/integration branch.
TEST_BRANCH="test"
MAIN_BRANCH="main"

# --- Safety Checks ---
# Exit immediately if a command exits with a non-zero status.
set -e

FEATURE_BRANCH=$1

# --- Step 1: Merge Feature into Test Branch ---
echo "--- Step 1: Merging '$FEATURE_BRANCH' into '$TEST_BRANCH' ---"
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

git checkout $TEST_BRANCH
git pull origin $TEST_BRANCH
git merge --no-ff $FEATURE_BRANCH -m "Merge branch '$FEATURE_BRANCH' into $TEST_BRANCH"
git push origin $TEST_BRANCH
echo "✅ '$TEST_BRANCH' is now up-to-date with your feature."
echo
