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
DEVELOP_BRANCH="develop"

# --- Safety Checks ---
# Exit immediately if a command exits with a non-zero status.
set -e

VERSION=$1
RELEASE_BRANCH="release/$VERSION"

# --- Step 1: Create Release Branch ---
echo "--- Step 1: Creating release branch '$RELEASE_BRANCH' from '$TEST_BRANCH' ---"
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

git checkout -b $RELEASE_BRANCH $TEST_BRANCH
git push -u origin $RELEASE_BRANCH
echo "✅ Release branch '$RELEASE_BRANCH' created and pushed."
echo "➡️ You should now deploy this branch to production."
echo

# --- Step 2: Merge Release into Main (Post-Deployment) ---
echo "--- Step 2: Merging '$RELEASE_BRANCH' into '$MAIN_BRANCH' and tagging ---"
echo "⚠️ IMPORTANT: Only continue if deployment was successful."
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

git checkout $MAIN_BRANCH
git pull origin $MAIN_BRANCH
git merge --no-ff $RELEASE_BRANCH -m "Merge release branch '$RELEASE_BRANCH' into $MAIN_BRANCH"
git tag -a $VERSION -m "Release version $VERSION"
git push origin $MAIN_BRANCH
git push origin $VERSION
echo "✅ '$MAIN_BRANCH' is now tagged and up-to-date with release '$VERSION'."
echo

# --- Step 4: Update Develop ---
echo "--- Step 4: Updating Develop with release ---"
read -p "Continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

git checkout $DEVELOP_BRANCH
git merge --no-ff $MAIN_BRANCH -m "Merge branch '$MAIN_BRANCH' into $DEVELOP_BRANCH"
git commit -a -m "Updating develop with release"
git push origin $DEVELOP_BRANCH
echo "✅ '$DEVELOP_BRANCH' is now fully up-to-date."
echo
echo "🎉 Release process complete!"
