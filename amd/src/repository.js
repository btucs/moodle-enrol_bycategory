import {call as fetchMany} from 'core/ajax';

export const getEnrolmentMethods = (courseid) => fetchMany([{
  methodname: 'enrol_bycategory_get_enrolment_methods',
  args: {courseid},
}])[0];
