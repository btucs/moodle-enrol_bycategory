// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

const getEnrolmentInfoCacheKey = (id) => {
    return 'PluginEnrolByCategory:' + id;
};

const getEnrolmentInfo = (id) => {
    const site = this.CoreSitesProvider.getCurrentSite();

    const params = {
        instanceid: id,
    };

    const preSets = {
        cacheKey: getEnrolmentInfoCacheKey(id),
    };

    return site.read('enrol_bycategory_get_instance_info', params, preSets);
};

const invalidateEnrolmentInfo = (id) => {
    const site = this.CoreSitesProvider.getCurrentSite();

    return site.invalidateWsCacheForKey(getEnrolmentInfoCacheKey(id));
};

const selfEnrol = async (courseId, password, instanceId, info) => {
    const site = this.CoreSitesProvider.getCurrentSite();

    const params = {
        courseid: courseId,
        password: password || '',
    };
    if (instanceId) {
        params.instanceid = instanceId;
    }

    return site.write('enrol_bycategory_enrol_user', params).then(async response => {

        if (response.status) {
            return true;
        }

        if (response.warnings && response.warnings.length) {
            // Invalid password warnings.
            const warning = response.warnings.find((warning) =>
                warning.warningcode == '2' || warning.warningcode == '3' || warning.warningcode == '4');

            if (warning) {
                throw new this.CoreWSError({ errorcode: this.CoreCoursesProvider.ENROL_INVALID_KEY, message: warning.message });
            } else {
                throw new this.CoreWSError(response.warnings[0]);
            }
        }

        throw Error('WS enrol_bycategory_enrol_user failed without warnings');
    });
};

const validatePassword = async (method, info) => {
    return this.CoreDomUtilsProvider.showModalLoading('core.loading', true).then(modal => {
        const result = {
            password: info.password || '',
        };

        const waitlistActive = info.waitlist;
        const canEnrol = info.waitlistcanenrol;

        if (waitlistActive && !canEnrol) {

            let confirmWaitlist = Promise.resolve();

            confirmWaitlist = this.CoreDomUtilsProvider.showConfirm(
                    this.TranslateService.instant('plugin.enrol_bycategory.joinwaitlistmessage'),
                    this.TranslateService.instant('plugin.enrol_bycategory.waitlist'),
                    this.TranslateService.instant('plugin.enrol_bycategory.joinwaitlist'),
            );

            return confirmWaitlist.then(() => {

                return selfEnrol(method.courseid, info.password, method.id, info).then(enroled => {

                    let alert = Promise.resolve();
                    alert = this.CoreDomUtilsProvider.showAlert(this.TranslateService.instant('plugin.enrol_bycategory.waitlist'),
                        this.TranslateService.instant('plugin.enrol_bycategory.waitlistadded'));

                    alert.then(() => {
                        result.waitlistadded = true
                        result.validated = false;

                        return result;
                    });

                }).catch(error => {
                    if (error && error.errorcode === this.CoreCoursesProvider.ENROL_INVALID_KEY) {
                        result.validated = false;
                        result.error = error.message;

                        return result;
                    }

                    this.CoreDomUtilsProvider.showErrorModalDefault(error, 'plugin.enrol_bycategory.errorselfenrol', true);

                    throw error;
                }).finally(() => {
                    modal.dismiss();
                });

            }).catch((error) => {
                    result.validated = false;
                    result.canceled = true;
                    modal.dismiss();
                    return result;
            });


        } else {
           return selfEnrol(method.courseid, info.password, method.id, info).then(enroled => {

                result.validated = enroled;

                return result;

            }).catch(async error => {
                if (error && error.errorcode === this.CoreCoursesProvider.ENROL_INVALID_KEY) {
                    result.validated = false;
                    result.error = error.message;

                    return result;
                }

                this.CoreDomUtilsProvider.showErrorModalDefault(error, 'plugin.enrol_bycategory.errorselfenrol', true);

                throw error;
            }).finally(() => {
                modal.dismiss();
            });
        }
    });
};

const performEnrol = (method, info) => {

    if (info.userwaitliststatus) {
        return false;
    }
    // Try to enrol without password.
    return validatePassword(method, info).then(response => {

            if (response.validated) {
                return true;
            }

            if (response.waitlistadded) {
                return false;
            }

            if (response.canceled) {
                return false;
            }

        // Ask for password.
        if (info.enrolpassword) {
            return this.CoreDomUtilsProvider.promptPassword({
                title: method.name,
                validator: (password) => {

                    info.password = password;

                    return validatePassword(method, info);
                },
                placeholder: 'plugin.enrol_bycategory.password',
                submit: 'core.courses.enrolme',
            }).then(response => {
                return response.validated;
            });
        } else {
            return false;
        }

    }).catch(() => {
        return false;
    });
};

var result = {
    getInfoIcons: async (courseId) => {

        return this.CoreEnrolService.getSupportedCourseEnrolmentMethods(courseId, 'bycategory').then(async enrolments => {

            if (!enrolments.length) {
                return [];
            }

            return getEnrolmentInfo(enrolments[0].id).then(info => {

                if (!info.enrolpassword) {
                    return [{
                        label: 'plugin.enrol_bycategory.pluginname',
                        icon: 'fas-right-to-bracket',
                    }];
                } else {
                    return [{
                        label: 'plugin.enrol_bycategory.pluginname',
                        icon: 'fas-key',
                    }];
                }
            });
        });
    },
    enrol: (method) => {
        return getEnrolmentInfo(method.id).then(info => {
            let promise = Promise.resolve();

            let waitlistActive = info.waitlist;
            let userWaitlistStatus = info.userwaitliststatus;
            let canEnrol = info.waitlistcanenrol;

            let message = this.TranslateService.instant('plugin.enrol_bycategory.confirmselfenrol');

            if (waitlistActive) {
                if (userWaitlistStatus) {
                    message = this.TranslateService.instant('plugin.enrol_bycategory.youareonthewaitlist');
                } else if (!canEnrol) {
                    message += '<br>' + this.TranslateService.instant('plugin.enrol_bycategory.waitlistmessage');
                }
            }

            if (!info.enrolpassword) {
                message += '<br>' +
                    this.TranslateService.instant('plugin.enrol_bycategory.nopassword');
            }

            promise = this.CoreDomUtilsProvider.showConfirm(
                    message,
                    method.name
                );

            return promise.then(() => {

                return performEnrol(method, info);
            }).catch(() => {
                return false;
            });
        });
    },
    invalidate: async(method) => {

        return invalidateEnrolmentInfo(method.id);
    },
};

result;